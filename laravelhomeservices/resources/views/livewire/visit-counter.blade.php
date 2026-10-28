{{-- MEJORA UI: indicador de analítica compacto y accesible. --}}
<div class="proyetech-visit-badge" role="status" aria-live="polite">
    <span>
        <i class="fa fa-chart-line" aria-hidden="true"></i>
        Visitas <span id="visit-count">{{ $visits ?? 0 }}</span>
    </span>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Increment the visit count when the page loads (POST: ruta con efecto secundario).
        axios.post('/increment-visit', {}, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(response => {
                // Update the visit count on the page
                document.getElementById('visit-count').textContent = response.data;
            })
            .catch(error => {
                console.error('Error updating visit count:', error);
            });
    </script>
</div>