<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">Members Only</div>

                <div class="p-6"><label>Trial Ends At: </label>{{ $details['trial_ends_at'] }}</div>
                <div class="p-6"><label>Ends At: </label>{{ $details['ends_at'] }}</div>

                <div class="p-6">
                    <form action="{{ route('members_resume.post') }}" method="post" id="resume-form">
                        @csrf
                        <x-jet-button class="mt-4">
                            Resume Subscribe Now
                        </x-jet-button>
                    </form>
                </div>


            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>

        <script>

            var form2 = document.getElementById('resume-form');
            form2.addEventListener('submit', async function(event) {
                event.preventDefault();
                var form2 = document.getElementById('resume-form');
                // Submit the form
                form2.submit();
            });


        </script>
    @endpush


</x-app-layout>
