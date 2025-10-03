<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Vote;
use Livewire\Component;
use App\Models\Position;
use App\Models\Candidate;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Events\VotedCandidate;
use Masmerise\Toaster\Toaster;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\VotingRoom as VotingRoomModel;

class VotingRoom extends Component
{
    use WithFileUploads;
    use WithPagination;

    

    public $room;
    public $positions = [];
    public $totalStudents = 0;
    

    // New position form
    public $newPosition = [
        'name' => '',
        'order_index' => 0,
    ];

    // New candidate form
    public $newCandidate = [
        'position_id' => null,
        'name' => '',
        'short_name' => '',
        'bio' => '',

    ];

    public $candidate_image;

    // Mount component
    public function mount($id)
    {
        $this->totalStudents = User::where('role', 'user')->count();
        $this->loadRoom($id);
    }


public $voters = [];

// public function showVoters($roomId)
// {
//     $this->room = VotingRoomModel::findOrFail($roomId);

//     // Get voters for this room
//     $this->voters = Vote::with('user')
//         ->where('voting_rooms_id', $this->room->id)
//         ->get();

//     $this->modal('voters-list')->show();
// }


    // ────────────────────────────────────────────────
    // Voting Logic
    // ────────────────────────────────────────────────

    public function voteCandidate($candidateId)
    {
        $candidate = Candidate::with('position')->findOrFail($candidateId);
        $userId = Auth::id();
        
        $roomStatus = $candidate->position->votingRoom->status;

        if ($roomStatus !== "Ongoing"){
            Toaster::error("Voting is not available at this time.  Status: {$roomStatus}");
            return;
        }

        // Prevent duplicate votes for the same position
        $alreadyVoted = Vote::where('user_id', $userId)
            ->where('position_id', $candidate->position_id)
            ->exists();

        if ($alreadyVoted) {
            Toaster::error('You already voted for this position.');
            return;
        }

        Vote::create([
            'user_id'      => $userId,
            'voting_rooms_id' => $candidate->position->votingRoom->id,
            'candidate_id' => $candidate->id,
            'position_id'  => $candidate->position_id,
        ]);
        event(new VotedCandidate($candidate->position->votingRoom->id));
        $this->loadRoom();
        Toaster::success('Vote cast successfully!');
    }

    // ────────────────────────────────────────────────
    // Room Loader & Live Vote Counts
    // ────────────────────────────────────────────────

public function loadRoom($id = null)
{
    $roomId = $id ?? $this->room->id;

    $this->room = VotingRoomModel::with([
        'positions' => fn($query) => $query->orderBy('order_index'),
        'positions.candidates.votes'
    ])->findOrFail($roomId);

    $this->updateStatusIfNeeded();

    // assign positions
    $this->positions = $this->room->positions;

    // count votes and sort candidates per position
    foreach ($this->positions as $position) {
        foreach ($position->candidates as $candidate) {
            $candidate->vote_count = $candidate->votes->count();
        }

        // sort descending by vote_count
        $position->setRelation(
            'candidates',
            $position->candidates->sortByDesc('vote_count')->values()
        );
    }
}


    public function deleteRoom($id)
    {
        VotingRoomModel::findOrFail($id)->delete();
        $this->modal('delete-room')->close();
        return redirect()->route('voting');
    }

    public function voters()
    {
        return Vote::with('user')
        ->where('voting_rooms_id', $this->room->id)
        ->select('user_id') // select only user_id for uniqueness
        ->distinct()
        ->get();

    }

    // ────────────────────────────────────────────────
    // Room Status Update (auto sync with time)
    // ────────────────────────────────────────────────

    public function updateStatusIfNeeded()
    {
        $now = Carbon::now();

        if ($this->room->status !== 'Closed' && $now->greaterThanOrEqualTo($this->room->end_time)) {
            $this->room->status = 'Closed';
        } elseif ($this->room->status !== 'Ongoing' && $now->between($this->room->start_time, $this->room->end_time)) {
            $this->room->status = 'Ongoing';
        } elseif ($this->room->status !== 'Pending' && $now->lessThan($this->room->start_time)) {
            $this->room->status = 'Pending';
        }

        $this->room->save();
    }

    public function getStatusTextColorProperty()
    {
        return match ($this->room->status) {
            'Pending'  => 'text-yellow-600 dark:text-yellow-400',
            'Ongoing'  => 'text-green-600 dark:text-green-400',
            'Closed'    => 'text-red-600 dark:text-red-400',
            default    => '',
        };
    }

    // ────────────────────────────────────────────────
    // Add Position
    // ────────────────────────────────────────────────

    protected $messages = [
    'newPosition.name.unique' => 'This position name already exists in this voting room.',
    'newCandidate.name.unique' => 'This candidate name already exists in this voting room.',
];


    public function createPosition()
    {
        $this->validate([
        'newPosition.name' => [
            'string',
            'required',
            Rule::unique('positions', 'name')
                ->where('voting_room_id', $this->room->id),
        ],
            'newPosition.order_index'  => 'nullable|integer|min:0',
        ]);

        $nextOrder = Position::where('voting_room_id', $this->room->id)->max('order_index') + 1;

        Position::create([
            'voting_room_id' => $this->room->id,
            'name'           => $this->newPosition['name'],
            'order_index'    => $this->newPosition['order_index'] ?? $nextOrder,
        ]);

        $this->reset('newPosition');
        Toaster::success('Position added successfully.');
        $this->modal('room-option')->close();
        $this->modal('add-positionOrcandidate')->close();
        $this->loadRoom();
        
    }

    // ────────────────────────────────────────────────
    // Add Candidate
    // ────────────────────────────────────────────────

    public function createCandidate()
    {
        $this->validate([
            'newCandidate.position_id' => 'required|exists:positions,id',
            'newCandidate.name' => [
            'string',
            'required',
            Rule::unique('candidates', 'name')
                ->where(fn ($q) => $q->where('position_id', $this->newCandidate['position_id'])),
            ],
            'newCandidate.short_name'  => 'nullable|string|max:50',
            'newCandidate.bio'         => 'nullable|string',
            'candidate_image'          => 'nullable|image|max:2048',

        ]);

        $photoPath = null;
        if ($this->candidate_image) {
            // store in storage/app/public/candidates
            // $photoPath = $this->candidate_image->store('candidates', 'public');

            $photoPath = $this->candidate_image->storePublicly('candidates', 'digitalocean');
        }

        Candidate::create([
            'position_id' => $this->newCandidate['position_id'],
            'name'        => $this->newCandidate['name'],
            'short_name'  => $this->newCandidate['short_name'],
            'bio'         => $this->newCandidate['bio'],
            'photo_url'   => $photoPath, // save relative path
        ]);

        $this->reset(['newCandidate', 'candidate_image']);
        $this->modal('room-option')->close();
        $this->modal('add-positionOrcandidate')->close();
        Toaster::success('Candidate added successfully.');
        $this->loadRoom();

    }

    // ────────────────────────────────────────────────
    // Room Options Modal
    // ────────────────────────────────────────────────

    public function roomOption()
    {
        $this->loadRoom();
        $this->modal('room-option')->show();
    }


    #[On('votedCandidate')]
    public function votedCandidate()
    {
        $this->loadRoom();
    }
    #[On('newUser')]
    public function newUser()
    {
        $this->loadRoom();
    }

    #[On('roomExpired')]
    public function roomExpired()
    {
        $this->loadRoom();
    }
    
    public Candidate|null $selectedCandidate = null;

    public function candidateCard($id)
    {
        $this->selectedCandidate = Candidate::findOrFail($id);
        $this->modal('candidate-card')->show();
    }

    public array $editCandidate = [];

    public function edit($id)
    {
        $this->editCandidate = Candidate::findOrFail($id)->toArray();
        $this->modal('edit-candidate')->show();
    } 

    public function updateCandidate()
    {
        // Validate the input
        $validated = $this->validate([
            'editCandidate.name' => 'required|string|max:255',
            'editCandidate.short_name' => 'nullable|string|max:50',
            'editCandidate.bio' => 'nullable|string',
            'candidate_image' => 'nullable|image|max:2048', // optional new image
        ]);

        // Find the candidate by ID
        $candidate = Candidate::findOrFail($this->editCandidate['id']);

        // Update candidate data
        $candidate->update([
            'name' => $this->editCandidate['name'],
            'short_name' => $this->editCandidate['short_name'] ?? null,
            'bio' => $this->editCandidate['bio'] ?? null,
        ]);

        // Handle new image upload
        if ($this->candidate_image) {
            // Delete old image if it exists
            if ($candidate->photo_url) {
                \Storage::disk('digitalocean')->delete($candidate->photo_url);
            }

            // Store new image
            $path = $this->candidate_image->storePublicly('candidates', 'digitalocean');
            $candidate->update(['photo_url' => $path]);
        }

        // Reset the form and modal state
        $this->reset(['candidate_image', 'editCandidate']);
        $this->modal('edit-candidate')->close();

        // Reload room to reflect updated candidates
        $this->loadRoom();

        // Optional: flash a success message
        \Masmerise\Toaster\Toaster::success('Candidate updated successfully!');
    }


    public $candidateToDelete = null;


    public function remove($id)
    {
       $this->candidateToDelete = $id;
        $this->modal('delete-candidate')->show();
    }

    public function deleteCandidate()
    {
        if (!$this->candidateToDelete) return;

        $candidate = Candidate::findOrFail($this->candidateToDelete);

        // Delete image if exists
        if ($candidate->photo_url) {
            \Storage::disk('digitalocean')->delete($candidate->photo_url);
        }

        $candidate->delete();

        $this->candidateToDelete = null; // reset
        $this->modal('delete-candidate')->close();
        $this->loadRoom();

        \Masmerise\Toaster\Toaster::success('Candidate removed successfully!');
    }




    // ────────────────────────────────────────────────
    // Render Component
    // ────────────────────────────────────────────────

    public function render()
    {

        
        
        return view('livewire.voting-room', [
            'room'      => $this->room,
            'positions' => $this->positions,
        ]);
    }
}
