<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            <!-- TrueLayer Integration Section -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">TrueLayer Integration</h3>

                @if(session('truelayer_status') === 'verified')
                    <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded">
                        Credentials verified successfully! Please complete Step 2 below.
                    </div>
                @endif
                
                @if(session('truelayer_status') === 'confirmed')
                    <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded">
                        TrueLayer integration is fully setup and confirmed!
                    </div>
                @endif
                
                @if(session('truelayer_status') === 'disconnected')
                    <div class="mb-4 text-sm text-yellow-600 bg-yellow-50 p-3 rounded">
                        TrueLayer integration disconnected.
                    </div>
                @endif

                @error('truelayer')
                    <div class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded">
                        {{ $message }}
                    </div>
                @enderror

                @php
                    $credential = Auth::user()->trueLayerCredential;
                @endphp

                @if(!$credential || !$credential->is_fully_setup)
                    <!-- Setup Process -->
                    <div class="space-y-6">
                        
                        <!-- Step 1: Input Credentials -->
                        <div class="border rounded-md p-4 {{ $credential ? 'opacity-50 pointer-events-none' : '' }}">
                            <h4 class="font-semibold text-md mb-2">Step 1: Enter TrueLayer Credentials</h4>
                            <p class="text-sm text-gray-600 mb-4">You can find these in your TrueLayer Developer Console.</p>
                            
                            <form action="{{ route('truelayer.verify') }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="client_id" value="Client ID" />
                                        <x-text-input id="client_id" name="client_id" type="text" class="mt-1 block w-full" required autocomplete="off" />
                                    </div>
                                    <div>
                                        <x-input-label for="client_secret" value="Client Secret" />
                                        <x-text-input id="client_secret" name="client_secret" type="password" class="mt-1 block w-full" required autocomplete="off" />
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <x-primary-button>{{ __('Verify Credentials') }}</x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Confirmation -->
                        @if($credential && !$credential->is_fully_setup)
                            <div class="border rounded-md p-4 border-indigo-300 bg-indigo-50">
                                <h4 class="font-semibold text-md mb-2 text-indigo-800">Step 2: Add URLs to TrueLayer Console</h4>
                                <p class="text-sm text-gray-700 mb-4">
                                    Your credentials are valid! Now, please copy the URLs below and paste them into your TrueLayer Developer Console.
                                </p>

                                <div class="space-y-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Redirect URI</label>
                                        <code class="block p-2 bg-white border rounded text-sm select-all mt-1">{{ url('/truelayer/callback') }}</code>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Webhook URI</label>
                                        <code class="block p-2 bg-white border rounded text-sm select-all mt-1">{{ url('/webhooks/truelayer') }}</code>
                                    </div>
                                </div>

                                <form action="{{ route('truelayer.confirm') }}" method="POST">
                                    @csrf
                                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                                        {{ __('I have pasted these in TrueLayer') }}
                                    </x-primary-button>
                                </form>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Connected State -->
                    <div class="flex items-center justify-between border rounded-md p-4 bg-gray-50">
                        <div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="font-semibold text-green-700">Connected to TrueLayer</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Your TrueLayer integration is fully configured and active.</p>
                        </div>
                        <form action="{{ route('truelayer.disconnect') }}" method="POST">
                            @csrf
                            <x-danger-button>
                                {{ __('Disconnect') }}
                            </x-danger-button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
