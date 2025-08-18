<?php include('../includes/registrar_header.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="z-10 inline-flex items-center p-2 mt-2 ms-3 text-sm text-white bg-blue-800 rounded-lg sm:hidden hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-300">
    <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
    <span class="sr-only">Open sidebar</span>
</button>

<div class="p-6 sm:ml-64 bg-gray-100 min-h-screen z-10">

  <div class="mb-6 mt-25 animation-fade-bottom">
    <h1 class="text-3xl font-bold text-blue-950 mb-5 animation-fade-bottom">Registrar Dashboard</h1>
  </div>

  <!-- Dashboard Cards -->
  <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 animation-fade-bottom">
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-blue-600">
      <h2 class="text-gray-600 text-sm font-medium">Enrolled Students</h2>
      <p class="text-2xl font-semibold text-gray-900" id="enrolledStudents">-</p>
    </div>
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-orange-500">
      <h2 class="text-gray-600 text-sm font-medium">Total Applications</h2>
      <p class="text-2xl font-semibold text-gray-900" id="totalApplications">-</p>
    </div>
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-blue-950">
      <h2 class="text-gray-600 text-sm font-medium">Pending Applications</h2>
      <p class="text-2xl font-semibold text-gray-900" id="pendingApplications">-</p>
    </div>
    <div class="bg-white p-5 rounded-lg shadow border-l-4 border-amber-500">
      <h2 class="text-gray-600 text-sm font-medium">Approved Applications</h2>
      <p class="text-2xl font-semibold text-gray-900" id="approvedApplications">-</p>
    </div>
  </div>

  <!-- Graphs -->
  <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Applications this week -->
    <div class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Applications This Week</h2>
      <canvas id="applicationsChart" height="200"></canvas>
    </div>

    <!-- Donut Chart -->
    <div class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Enrollment by Year Level</h2>
      <canvas id="enrolledDonut" height="200"></canvas>
    </div>
  </div>

  <!-- Recent Applications Table -->
  <div class="mt-10 animation-fade-bottom">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Applications</h2>
    <div class="bg-white rounded-lg shadow p-4 overflow-x-auto">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
          <tr>
            <th class="px-4 py-2">Application ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Course</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody id="recentApplicationsTable">
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
              <p class="mt-2">Loading applications...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div> <!-- closes main content container -->

<script>
document.addEventListener('DOMContentLoaded', function () {
  fetchDashboardStats();
  fetchWeeklyApplications();
  fetchEnrollmentByYearLevel();
  fetchRecentApplications();
});

function fetchDashboardStats() {
  fetch('/ncst/functions/dashboard_functions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_dashboard_stats'
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('enrolledStudents').textContent = data.data.enrolled_students;
      document.getElementById('totalApplications').textContent = data.data.total_applications;
      document.getElementById('pendingApplications').textContent = data.data.pending_applications;
      document.getElementById('approvedApplications').textContent = data.data.approved_applications;
    } else {
      console.error('Dashboard stats error:', data.message);
    }
  })
  .catch(err => console.error('Fetch dashboard stats failed:', err));
}

function fetchWeeklyApplications() {
  fetch('/ncst/functions/dashboard_functions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_weekly_applications'
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      const labels = data.data.map(item => item.date);
      const counts = data.data.map(item => item.count);

      const canvas = document.getElementById('applicationsChart');
      if (!canvas) return console.warn('applicationsChart canvas not found');

      const ctx = canvas.getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Applications',
            data: counts,
            backgroundColor: 'rgba(37, 99, 235, 0.7)'
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } }
        }
      });
    } else {
      console.error('Weekly applications error:', data.message);
    }
  })
  .catch(err => console.error('Fetch weekly applications failed:', err));
}

function fetchEnrollmentByYearLevel() {
  fetch('/ncst/functions/dashboard_functions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_enrollment_by_year_level'
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      const labels = data.data.map(item => item.year_level);
      const counts = data.data.map(item => item.count);
      const total = counts.reduce((a, b) => a + b, 0);

      const canvas = document.getElementById('enrolledDonut');
      if (!canvas) return console.warn('enrolledDonut canvas not found');

      const ctx = canvas.getContext('2d');
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: counts,
            backgroundColor: ['#2563eb', '#22c55e', '#f59e0b', '#ef4444']
          }]
        },
        options: {
          responsive: true,
          cutout: '70%',
          plugins: {
            legend: { position: 'bottom' },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let value = context.raw;
                  let percentage = ((value / total) * 100).toFixed(1);
                  return `${context.label}: ${value} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    } else {
      console.error('Enrollment chart error:', data.message);
    }
  })
  .catch(err => console.error('Fetch enrollment chart failed:', err));
}

function fetchRecentApplications() {
  fetch('/ncst/functions/dashboard_functions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=get_recent_applications&limit=10'
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.getElementById('recentApplicationsTable');
    if (!tbody) return console.warn('recentApplicationsTable not found');

    tbody.innerHTML = '';

    if (data.status === 'success') {
      data.data.forEach(app => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="px-4 py-2">${app.application_id}</td>
          <td class="px-4 py-2">${app.name}</td>
          <td class="px-4 py-2">${app.course}</td>
          <td class="px-4 py-2">${app.date}</td>
          <td class="px-4 py-2">${app.status}</td>
        `;
        tbody.appendChild(row);
      });
    } else {
      console.error('Recent applications error:', data.message);
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500 py-4">Failed to load applications</td></tr>`;
    }
  })
  .catch(err => {
    console.error('Fetch recent applications failed:', err);
    const tbody = document.getElementById('recentApplicationsTable');
    if (tbody) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500 py-4">Error loading applications</td></tr>`;
    }
  });
}
</script>

<?php include('../includes/footer.php'); ?>