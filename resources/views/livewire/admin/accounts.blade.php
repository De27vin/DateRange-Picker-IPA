<div class="w-full px-4 mx-auto max-w-3xl" style="margin-top: 16rem;">
    <ul role="list" class=" overflow-hidden bg-transparent">
        @foreach($accounts as $id => $account)
            <li wire:click="setAccount({{$id}})" class="relative cursor-pointer">
                <div class="relative flex h-16 my-4 w-full items-center">
                    <div class=" relative h-full w-full flex items-center shadow-md">
                        <div class="flex h-full items-center justify-center w-64" style="background-color: {{$account['theme']['backgroundcolor'] ?? '#ffffff'}};">
                            <img alt="Account Logo" class="h-10 lg:block w-auto" src="/assets/themes/{{ $account['slug'] }}/images/logo.png"/>
                        </div>
                        <div class="flex items-center h-full w-full justify-end gap-x-4 pr-8 bg-white bg-opacity-40 hover:bg-opacity-100">
                            <div class=" flex flex-col items-end">
                                <p class="text-sm leading-6 text-gray-900">{{$account['name']}}</p>
                            </div>
                            <svg class="h-5 w-5 flex-none text-gray-900" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="text-gray-400" style="position: fixed; bottom: 0.25rem; right: 0.5rem;">v{{ config('ucp.version') }}</div>
</div>