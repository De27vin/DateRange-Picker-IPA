@props(['active', 'type' => '', 'href' => '#'])
@php
    $classes = ($active ?? false)
        ? 'px-0'
        : 'px-0';
    $classesLink = ($active ?? false)
        ? 'rounded-none justify-start text-left bg-color-new text-white px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer'
        : 'rounded-none justify-start text-left px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer';
@endphp


@if($type == 'title')
    @php
        $classes = ($active ?? false)
            ? 'active px-1 h-8 w-full uppercase flex items-center my-2 py-2 rounded-full leading-5 text-white bg-color-new'
            : 'w-full px-1 h-8 flex items-center my-2 py-2 uppercase text-gray-400 rounded-full leading-5';
    @endphp
        <div {{ $attributes->merge(['type' => 'title', 'class' => $classes]) }} role="menuitem">
            <div class="px-4 w-full text-left"  }}>
                {{ $slot }}
            </div>
        </div>
        <div class="border-b border-gray-600 h-1 flex w-full px-0 "></div>
    {{-- <p class="text-bold m-0 pb-2 pt-12 lg:pt-2 px-6 h-12 flex items-end border-b border-gray-600">{{$slot}}</p> --}}
{{-- @elseif($type == 'logout')
    <div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <div {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }} 
                role="menuitem"
                onclick="event.preventDefault();
                this.closest('form').submit();">
                <a {{ $attributes->merge(['class' => $classesLink]) }}
                    href="{{route('logout')}}">
                    {{ $slot }}
                </a>
            </div>
        </form>
    </div>
 --}}
 @elseif($type == 'languages' || $type == 'account')
    <div class="px-0">
        <div {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }} role="menuitem">
            <a {{ $attributes->merge(['href' => $href, 'class' => $classesLink]) }}>
                {{ $slot }}
            </a>
        </div>
    </div>
@elseif($type == 'button')
    @php
        $classes = ($active ?? false)
            ? 'active cursor-pointer px-1 w-full flex items-center my-2 py-2 rounded-full leading-5 text-white bg-color-new hover:text-white hover:bg-color-new focus:outline-none'
            : 'w-full cursor-pointer px-1 flex items-center my-2 py-2 rounded-full leading-5 text-secondary-600 hover:text-gray-200 hover:bg-color-new focus:outline-none focus:text-gray-200 focus:bg-color-new';
    @endphp
    <div class="px-2">
        <div {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }} role="menuitem">
            <div class="px-4 w-full text-left"  }}>
                {{ $slot }}
            </div>
        </div>
    </div>
@else
    @php
        $classes = ($active ?? false)
            ? 'active cursor-pointer px-1 w-full flex items-center py-2 leading-5 text-white bg-color-new hover:text-white hover:bg-color-new focus:outline-none'
            : 'w-full cursor-pointer px-1 flex items-center py-2 leading-5 text-secondary-600 hover:text-gray-200 hover:bg-color-new focus:outline-none focus:text-gray-200 focus:bg-color-new';
        $classButton = 'mx-0 px-0 py-0 rounded-none w-full';
    @endphp
    <div class="px-0">
        <button {{ $attributes->merge(['type' => 'button', 'class' => $classButton]) }} role="menuitem">
            <a class="normal-case rounded-none justify-start text-left px-4 py-2 mx-0 w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer block" {{ $attributes->merge(['href' => $href]) }}>
                {{ $slot }}
            </a>
        </button>
    </div>
@endif
