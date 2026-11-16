{{-- Texto exacto del permiso de publicación que acepta un proveedor. Se edita en /admin/legal; un cambio de fondo publica una nueva versión. --}}
{{ \App\Support\Content::fill((string) config('content.legal.consent')) }}
