{{-- MEJORA UI: indicador de analítica compacto y accesible. --}}
<div class="proyetech-visit-badge" role="status" aria-live="polite">
    <span>
        <i class="fa fa-chart-line" aria-hidden="true"></i>
        Visitas <span id="visit-count">{{ $visits ?? 0 }}</span>
    </span>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Increment the visit count when the page loads
    axios.get('/increment-visit')
        .then(response => {
            // Update the visit count on the page
            document.getElementById('visit-count').textContent = response.data;
        })
        .catch(error => {
            console.error('Error updating visit count:', error);
        });
</script>
