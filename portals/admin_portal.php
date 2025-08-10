<?php include('../includes/admin_header.php'); ?>

<button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="mt-25 inline-flex items-center p-2 mt-2 ms-3 text-sm text-white bg-blue-800 rounded-lg sm:hidden hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-300">
    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="9" height="16" viewBox="0 0 9 16"><path fill="currentColor" d="M7.62 7.18L2.79 3.03c-.7-.6-1.79-.1-1.79.82v8.29c0 .93 1.09 1.42 1.79.82l4.83-4.14c.5-.43.5-1.21 0-1.64"/></svg>
    <span class="sr-only">Open sidebar</span>
</button>

<div class="p-6 sm:ml-64 bg-gray-100 min-h-screen">
  <div class="max-w-7xl mx-auto bg-white p-6 mt-15 rounded shadow-lg animation-fade-bottom">
    <h1 class="text-3xl font-bold text-blue-800 mb-3">Admin Dashboard</h1>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-9 text-sm">
    <!-- Total Users -->
    <div class="bg-blue-100 rounded-lg shadow p-5 animation-fade-bottom">
      <h2 class="text-sm font-medium text-blue-800 uppercase mb-1">Total Users</h2>
      <p class="text-3xl font-bold text-blue-900" id="totalUsers">Loading...</p>
    </div>

    <!-- Students -->
    <div class="bg-blue-200 rounded-lg shadow p-5 animation-fade-bottom">
      <h2 class="text-sm font-medium text-blue-900 uppercase mb-1">Students</h2>
      <p class="text-3xl font-bold text-blue-950" id="studentCount">Loading...</p>
    </div>

    <!-- Registrars -->
    <div class="bg-yellow-200 rounded-lg shadow p-5 animation-fade-bottom">
      <h2 class="text-sm font-medium text-yellow-900 uppercase mb-1">Registrars</h2>
      <p class="text-3xl font-bold text-yellow-950" id="registrarCount">Loading...</p>
    </div>

    <!-- Treasury -->
    <div class="bg-yellow-300 rounded-lg shadow p-5 animation-fade-bottom">
        <!-- Treasury -->
        <div>
          <h2 class="text-xs font-medium text-yellow-950 uppercase mb-1">Treasury</h2>
          <p class="text-2xl font-bold text-yellow-900" id="treasuryCount">Loading...</p>
        </div>
    </div>
  </div>
</div>
</div>

<script>
  // Load user counts when page loads
  document.addEventListener('DOMContentLoaded', function() {
    loadUserCounts();
  });

  function loadUserCounts() {
    const formData = new FormData();
    formData.append('action', 'get_user_counts');

    fetch('/ncst/functions/user_count_functions.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      document.getElementById('totalUsers').textContent = data.total_users;
      document.getElementById('studentCount').textContent = data.students;
      document.getElementById('registrarCount').textContent = data.registrars;
      document.getElementById('treasuryCount').textContent = data.treasury;
    })
    .catch(error => {
      console.error('Error loading user counts:', error);
      // Set default values if there's an error
      document.getElementById('totalUsers').textContent = '0';
      document.getElementById('studentCount').textContent = '0';
      document.getElementById('registrarCount').textContent = '0';
      document.getElementById('treasuryCount').textContent = '0';
    });
  }

  function toggleStatus() {
    const btn = document.getElementById("statusBtn");
    const isActive = btn.textContent === "Activate";
    btn.textContent = isActive ? "Deactivate" : "Activate";
    btn.className = isActive 
      ? "bg-gray-500 text-white px-3 py-1 rounded"
      : "bg-green-500 text-white px-3 py-1 rounded";
  }

  function addUser() {
    document.getElementById('addUserModal').classList.add('hidden');
    // Reload user counts after adding user
    loadUserCounts();
  }

    const roleOptions = {
    student: ['student'],
    employee: ['registrar', 'treasury', 'admin']
  };

  function updateRoles() {
    const userType = document.getElementById('userTypeSelect').value;
    const roleSelect = document.getElementById('roleSelect');
    const dynamicFields = document.getElementById('dynamicFields');

    // Clear previous roles and fields
    roleSelect.innerHTML = '<option value="" disabled selected>Select Role</option>';
    dynamicFields.innerHTML = '';

    if (roleOptions[userType]) {
      roleOptions[userType].forEach(role => {
        const option = document.createElement('option');
        option.value = role;
        option.textContent = capitalize(role);
        roleSelect.appendChild(option);
      });
    }
  }

  function handleRoleChange() {
    const role = document.getElementById('roleSelect').value;
    const dynamicFields = document.getElementById('dynamicFields');
    dynamicFields.innerHTML = ''; // Clear previous

    // Add fields based on role
    if (role === 'student') {
      dynamicFields.innerHTML = `
        <input type="text" placeholder="Student ID" class="w-full border px-3 py-2 rounded mb-3" required>
        <input type="text" placeholder="Course" class="w-full border px-3 py-2 rounded mb-3" required>
        <input type="text" placeholder="Year Level" class="w-full border px-3 py-2 rounded mb-3" required>
      `;
    } else if (role === 'registrar' || role === 'treasury') {
      dynamicFields.innerHTML = `
        <input type="text" placeholder="Employee ID" class="w-full border px-3 py-2 rounded mb-3" required>
      `;
    } else if (role === 'department head') {
      dynamicFields.innerHTML = `
        <input type="text" placeholder="Head ID" class="w-full border px-3 py-2 rounded mb-3" required>
        <input type="text" placeholder="Department Name" class="w-full border px-3 py-2 rounded mb-3" required>
      `;
    } else if (role === 'admin') {
      dynamicFields.innerHTML = `
        <input type="text" placeholder="Admin ID" class="w-full border px-3 py-2 rounded mb-3" required>
      `;
    }
  }

  function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function addUser() {
    alert('User added!');
    document.getElementById('addUserModal').classList.add('hidden');
    // Reload user counts after adding user
    loadUserCounts();
  }
</script>

<?php include('../includes/footer.php'); ?>