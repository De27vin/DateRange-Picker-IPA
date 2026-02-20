<div class="px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto" style="margin-top: 16rem;">
    <div class=" overflow-hidden shadow-lg bg-gray-300 bg-opacity-20">
        <div class="flex flex-col max md:flex-row md:flex-1 lg:max-w-screen-md">
            <div class="p-8 md:w-1/2 md:flex-shrink-0 md:flex md:flex-col items-start">
                <a href="https://serv24.com/en/solutions/ucp">
                    <h3 class="mb-4">UCP</h3>
                    <span class="text-base">Universal Convergence Platform</span>
                </a>
                <p class="mt-6 opacity-70 md:mt-0">
                    @lang('You have been invited as a user to access the ucp application.')<br/>
                    @lang('Set your password here.')<br/><br/>
                    @lang('After that you can log in to ucp normally with your data.')
                </p>
            </div>
            <div class="p-8 md:flex-1">
                <h3 class="mb-4">
                    @lang('Set Password')
                </h3>
                {{ $slot }}
            </div>
        </div>
        <div class="w-full px-8 py-1 ">
            <div class="w-full border-t border-gray-400"></div>
            <div class="text-xs float-right inline-block items-end my-4">
                <a class="" href="https://serv24.com/terms">
                    @lang('Terms & Conditions')
                </a>
            </div>

        </div>
    </div>
</div>
