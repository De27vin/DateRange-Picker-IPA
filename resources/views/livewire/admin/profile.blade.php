<div class="flex w-full">

        <div class="relative tabgroup-vertical mr-24">
            @foreach($tabs as $title => $tabData)
                <div :key="{{$title}}" wire:click.prevent="setProfileTab('{{$title}}')" for="$title" class="tabitem @if($tabData['active'] == 1) active @endif ">{{ __($tabData['title']) }}</div>
                @php
                    if($tabData['active'] == 1){
                        $tabContent = $tabContentPath . $tabData['content'];
                        $data = $editing;
                    }
                @endphp
            @endforeach
        </div>
        <div class="tabContent w-full">
            <x-dynamic-component :component="$tabContent" :data="$data" />
        </div>

</div>
