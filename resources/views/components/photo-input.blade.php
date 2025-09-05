@props(['name','label','required'=>false])

<div x-data="filePreview()" x-init="init()">
    <x-input-label for="{{ $name }}" :value="$label" />
    <input id="{{ $name }}" name="{{ $name }}" type="file"
           accept=".jpg,.jpeg,.png,.webp"
           class="mt-1 block w-full text-sm"
           {{ $required ? 'required' : '' }}
           @change="pick($event)">
    <template x-if="url">
        <div class="mt-2">
            <img :src="url" alt="prévia {{ $label }}"
                 class="h-28 w-auto rounded border object-cover">
        </div>
    </template>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
