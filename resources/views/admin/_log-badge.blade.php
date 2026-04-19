<span class="admin-badge admin-badge--{{ $log->type }}">
    @if($log->type === 'search') 🔍
    @elseif($log->type === 'login') 🔑
    @elseif($log->type === 'account') 👤
    @elseif($log->type === 'quote') 💰
    @elseif($log->type === 'security') 🚨
    @else 🎮
    @endif
    {{ ucfirst($log->type) }}
</span>
