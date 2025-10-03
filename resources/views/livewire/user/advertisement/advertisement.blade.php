<div 
    x-init="Echo.channel('manage-ads')
                .listen('.ads.post', (e) => {
                    console.log('new ads post', e.ads);
                    Livewire.dispatch('newAdPosted');
                });
            Echo.channel('manage-feeds')
                .listen('.feed.post', (e) => {
                    console.log('new feed post', e.feed);
                    Livewire.dispatch('newFeedPosted');
                });
                
            "
class="space-y-4">
    
    <div>
            <flux:heading size="xl" level="1">{{ __('Advertisement') }}</flux:heading>
            <flux:subheading size="lg" class="mb-6">
                {{ __('Manage your profile and account settings') }}
            </flux:subheading>
        


        <flux:separator variant="subtle" class="col-span-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-5 gap-6 h-full ">
        <div class="w-full col-span-3 space-y-4 px-4 sm:px-6 md:px-10 lg:px-15 xl:px-20">
            
            <div class="flex justify-between">
                <flux:dropdown class="flex items-center">
                    <flux:button icon:trailing="chevron-down" size="sm">
                        {{ $organizationFilter ? ucfirst($organizationFilter) : 'All Organizations' }}
                    </flux:button>

                    <span class="text-xs flex gap-2 p-2">
                        <flux:icon.bars-3-bottom-left class="size-4" />
                        {{ count($this->filteredAdvertisements) }} Active Advertisement
                    </span>

                    <flux:menu>
                        <flux:menu.item wire:click="$set('organizationFilter', null)">
                            All Organizations
                        </flux:menu.item>
                        @foreach($orgs as $org)
                            <flux:menu.item wire:click="$set('organizationFilter', '{{ $org->name }}')">
                                {{ $org->name }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

                @if ($organizationFilter)
                    <flux:button color="gray" size="sm" wire:click="resetFilters">
                        Reset Filters
                    </flux:button>
                @endif
            </div>
                
            

            {{-- ADVERTISEMENTS --}}
            @include('livewire.user.advertisement.partials.advertisement-feed') 

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="flex flex-col gap-6 col-span-2">
            <!-- QUICK STATS -->
            <div class="border w-full p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                <h2 class="font-semibold mb-3">📊 Quick Stats</h2>
                <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                    <li>Total Advertisements: <strong>{{ $stats['total_ads'] }}</strong></li>
                    <li>Events: <strong>{{ $stats['events'] }}</strong></li>
                    <li>Internships: <strong>{{ $stats['internships'] }}</strong></li>
                    <li>Jobs: <strong>{{ $stats['jobs'] }}</strong></li>
                    <li>Scholarships: <strong>{{ $stats['scholarships'] }}</strong></li>
                </ul>
            </div>

            <!-- TRENDING ORGS -->
            <div class="border w-full p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                <h2 class="font-semibold mb-3">🔥 Trending Organizations</h2>
                @forelse($trendingOrgs as $org)
                    <div class="flex justify-between text-sm py-1 px-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                        <span class="truncate">{{ $org->organization }}</span>
                        <span class="text-gray-500">{{ $org->ad_count }} posts</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No data available.</p>
                @endforelse
            </div>

            <!-- HELP & SUPPORT -->
            <div class="border w-full p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                <h2 class="font-semibold mb-3">💬 Help & Support</h2>
                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <li><a href="#" class="hover:underline">📘 How to post an advertisement</a></li>
                    <li><a href="#" class="hover:underline">📩 Contact administrator</a></li>
                    <li><a href="#" class="hover:underline">⚙️ Manage your organization</a></li>
                </ul>
            </div>
        </div>
    </div>


</div>
