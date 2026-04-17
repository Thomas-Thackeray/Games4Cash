<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Debug Error</title>
<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem}h1{color:#e63946}pre{background:#222;padding:1rem;border-radius:6px;overflow:auto;white-space:pre-wrap;word-break:break-all}</style>
</head>
<body>
<h1>Error in {{ $context }}</h1>
<pre>{{ $error }}</pre>
<pre style="font-size:0.8rem;color:#aaa">{{ $file }}</pre>
<p><a href="/admin" style="color:#4ea8de">← Admin</a></p>
</body>
</html>
