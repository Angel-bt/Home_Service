<div>
    <div class="admin-dashboard">
        <!-- Barra de navegación del dashboard -->
        <div class="dashboard-nav card p-3 mb-4">
            <h2>Admin Dashboard</h2>
        </div>

        <!-- Sección de estadísticas rápidas -->
        <div class="row">
            <div class="col-md-3">
                <div class="card stat-card p-3 text-center">
                    <h3>Total Users</h3>
                    <p>{{ $totalUsers ?? 0 }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 text-center">
                    <h3>Total Services</h3>
                    <p>{{ $totalServices ?? 0 }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 text-center">
                    <h3>Total Categories</h3>
                    <p>{{ $totalServiceCategory ?? 0 }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 text-center">
                    <h3>Total Contacts</h3>
                    <p>{{ $totalContacts ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Nueva tarjeta para el contador de visitas -->
        <div class="row custom-margin">
            <div class="col-md-3">
                <div class="card stat-card p-3 text-center">
                    <h3>Total Visits</h3>
                    <p id="visit-count">{{ $visits->count() ?? 0 }}</p>
                    <button id="reset-visit-button" class="btn btn-danger mt-2">Reset Visits</button>
                </div>
            </div>
        </div>

        <!-- Sección de la tabla de visitas -->
        <div class="card p-3 mt-4">
            <h3>Visitas Registradas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Visitante</th>
                        <th>Fecha de Visita</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visits as $visit)
                        <tr>
                            <td>{{ $visit->id }}</td>
                            <td>{{ $visit->visitor_name }}</td>
                            <td>{{ $visit->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Sección de gráficos -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h3>User Growth</h3>
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h3>Service Categories Popularity</h3>
                    <canvas id="servicePopularityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sección de últimas actividades -->
        <div class="card p-3 mt-4">
            <h3>Recent Activities</h3>
            <ul>
                @forelse ($recentActivities ?? [] as $activity)
                    <li>{{ $activity->description }} - <small>{{ $activity->created_at->diffForHumans() }}</small></li>
                @empty
                    <li>No recent activities.</li>
                @endforelse
            </ul>
        </div>

        <!-- Sección de enlaces rápidos -->
        <div class="card p-3 mt-4">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ route('admin.service_categories') }}">Manage Service Categories</a></li>
                <li><a href="{{ route('admin.all_services') }}">Manage Services</a></li>
                <li><a href="{{ route('admin.slider') }}">Manage Slider</a></li>
                <li><a href="{{ route('admin.contacts') }}">View Contacts</a></li>
                <li><a href="{{ route('admin.service_providers') }}">Manage Service Providers</a></li>
            </ul>
        </div>
    </div>

    <!-- Scripts para gráficos (usando Chart.js) -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Gráfico de crecimiento de usuarios
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'User Growth',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        // Gráfico de popularidad de categorías de servicios
        const servicePopularityCtx = document.getElementById('servicePopularityChart').getContext('2d');
        const serviceCategories = @json($serviceCategories); // Pasar datos de PHP a JavaScript

        const labels = serviceCategories.map(category => category.name);
        const servicesCount = serviceCategories.map(category => category.services_count);

        new Chart(servicePopularityCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Services',
                    data: servicesCount,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Lógica para reiniciar el contador de visitas
        document.getElementById('reset-visit-button').addEventListener('click', function () {
            if (confirm('¿Estás seguro de que deseas reiniciar el contador de visitas?')) {
                axios.post('/reset-visits', {}, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Token CSRF para protección
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.data.success) {
                        // Actualizar el contador en la interfaz
                        document.getElementById('visit-count').textContent = 0;
                        alert('El contador de visitas se ha reiniciado correctamente.');
                    } else {
                        alert('Error al reiniciar el contador de visitas.');
                    }
                })
                .catch(error => {
                    console.error('Error resetting visit count:', error);
                    alert('Error al reiniciar el contador de visitas.');
                });
            }
        });
    </script>


    @endpush
    
</div>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 12px;
        text-align: left;
    }
    th {
        background-color: #f8f9fa;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
</style>