<span class="admin-badge admin-badge--{{ $log->type }}">
    @if($log->type === 'search') 🔍
    @elseif($log->type === 'login') 🔑
    @elseif($log->type === 'quote') 💰
    @elseif($log->type === 'security') 🚨
    @elseif($log->type === 'suspicious') ⚠️
    @else 🎮
    @endif
    {{ ucfirst($log->type) }}
</span>
