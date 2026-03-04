@php
    $isActive = $node['id'] === $currentId;
    $paddingLeft = $depth * 1; // rem
    $type = $node['type'] ?? 'default';
    $typeColors = [
        'root' => 'text-purple-600',
        'division' => 'text-blue-600',
        'project' => 'text-green-600',
        'site' => 'text-gray-600',
        'default' => 'text-gray-600',
    ];
    $typeColor = $typeColors[$type] ?? 'text-gray-600';
@endphp

<button
    wire:click="switchTo({{ $node['id'] }})"
    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center gap-2 {{ $isActive ? 'bg-gray-100 font-medium' : '' }}"
    style="padding-left: {{ 1 + $paddingLeft }}rem;"
>
    @if($type === 'root')
        <svg class="h-4 w-4 {{ $typeColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
    @elseif($type === 'division')
        <svg class="h-4 w-4 {{ $typeColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
    @elseif($type === 'project')
        <svg class="h-4 w-4 {{ $typeColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
        </svg>
    @else
        <svg class="h-4 w-4 {{ $typeColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
    @endif
    <span class="{{ $isActive ? 'text-gray-900' : 'text-gray-700' }}">{{ $node['name'] }}</span>
    @if($isActive)
        <svg class="h-4 w-4 ml-auto text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
    @endif
</button>

@foreach($node['children'] as $child)
    @include('hierarchical-scope::livewire.partials.scope-tree-item', ['node' => $child, 'depth' => $depth + 1])
@endforeach
