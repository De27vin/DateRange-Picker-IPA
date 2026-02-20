<span class="flex">
    @if($healthState['pending'])
        <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
        <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
        <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
    @else
        @if($healthState['success'])
            <x-monoicon.stop :class="'text-green-600'"></x-monoicon.stop>
        @else
            <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
        @endif
        @if($healthState['warning'])
            <x-monoicon.stop :class="'text-orange-600'"></x-monoicon.stop>
        @else
            <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
        @endif
        @if($healthState['error'])
            <x-monoicon.stop :class="'text-red-600'"></x-monoicon.stop>
        @else
            <x-monoicon.stop :class="'text-gray-400'"></x-monoicon.stop>
        @endif
    @endif
</span>
