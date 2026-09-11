<div style="display: flex; align-items: center; justify-content: center;
            
            color: white; 
            font-family: 'Courier New', monospace; font-size: 16px; 
            padding: 12px; border-radius: 12px; 
            width: 180px; text-align: center; 
            box-shadow: 0 4px 8px rgba(5, 96, 233, 0.2); 
            border: 2px solid #ff1493; transition: transform 0.3s ease-in-out;">
    <span>
        <i class="fa fa-users" style="color: white; font-size: 20px; animation: pulse 1.5s infinite;"></i>
        Visitas: <span id="visit-count">{{ $visits ?? 0 }}</span>
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

<style>
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            transform: scale(1);
        }
    }
</style>