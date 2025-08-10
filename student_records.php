<?php 
include('includes/records_header.php');
?>

<!-- Vue.js App Container -->
<div id="studentRecordsApp" class="p-4 md:ml-64 mt-20">
  <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Student Records</h2>
    
    <!-- Message Display -->
    <div v-if="message" :class="{
      'bg-green-100 border-green-400 text-green-700': messageType === 'success',
      'bg-red-100 border-red-400 text-red-700': messageType === 'error',
      'bg-yellow-100 border-yellow-400 text-yellow-700': messageType === 'warning',
      'bg-blue-100 border-blue-400 text-blue-700': messageType === 'info'
    }" class="px-4 py-3 rounded relative mb-4">
      {{ message }}
    </div>
    
    <!-- Search Section -->
    <div class="mb-6">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="flex-1">
          <label for="studentNoSearch" class="block text-sm font-medium text-gray-700 mb-2">Search by Student Number</label>
          <input 
            type="text" 
            id="studentNoSearch"
            v-model="searchStudentNo" 
            @keyup.enter="searchStudent"
            placeholder="Enter student number (e.g., 2025-00001)" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
        <div class="flex gap-2">
          <button @click="searchStudent" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Search
          </button>
          <button @click="clearSearch" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
            Clear
          </button>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Searching...</p>
    </div>
    
    <!-- Student Information Table -->
    <div v-if="studentInfo && !loading" class="overflow-x-auto">
      <h3 class="text-lg font-semibold mb-4 text-gray-800">Student Information</h3>
      
      <!-- Student Details Table -->
      <table class="w-full border-collapse border border-gray-300 mb-6">
        <thead>
          <tr class="bg-blue-950 text-white">
            <th class="border border-gray-300 px-4 py-2 text-left">Field</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Student Number</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.studentNo }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Full Name</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.fullName }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Email</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.email }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Course</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.course || 'Not specified' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Year Level</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.yearLevel || 'Not specified' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Date Created</td>
            <td class="border border-gray-300 px-4 py-2">{{ formatDate(studentInfo.dateCreated) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- No Results Message -->
    <div v-if="!loading && !studentInfo && hasSearched" class="text-center py-8 text-gray-500">
      <p>No student found with the provided Student Number.</p>
      <p class="text-sm mt-2">Please check the Student Number and try again.</p>
    </div>
    
    <!-- Initial State -->
    <div v-if="!loading && !studentInfo && !hasSearched" class="text-center py-8 text-gray-500">
      <p>Enter a Student Number above to search for student records.</p>
    </div>
  </div>
</div>

<!-- Vue.js -->
<script src="/ncst/js/vue.global.js"></script>
<script src="/ncst/js/student_records_vue.js"></script>

<?php include('includes/footer.php');?>
