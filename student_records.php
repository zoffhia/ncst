<?php 
include('includes/registrar_header.php');
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
            placeholder="Enter student number" 
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
      <div class="relative flex justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Student Information</h3>
          <button @click="toggleExportDropdown" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export
          </button>
          
          <!-- Export Dropdown -->
          <div v-show="showExportDropdown" class="absolute bottom-full right-0 mb-2 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-48">
            <div class="py-1">
              <button @click="exportAs('pdf')" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36c.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05a12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064a.44.44 0 0 1-.06.2a.3.3 0 0 1-.094.124a.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822c.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/>
                  <path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2z"/>
                </svg>
                PDF
              </button>
              <button @click="exportAs('word')" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M21.17 3.25q.33 0 .59.25q.24.24.24.58v15.84q0 .34-.24.58q-.26.25-.59.25H7.83q-.33 0-.59-.25q-.24-.24-.24-.58V17H2.83q-.33 0-.59-.24Q2 16.5 2 16.17V7.83q0-.33.24-.59Q2.5 7 2.83 7H7V4.08q0-.34.24-.58q.26-.25.59-.25m-.8 8.09l1.2 3.94H9.6l1.31-6.56H9.53l-.78 3.88l-1.11-3.75H6.5l-1.19 3.77l-.78-3.9H3.09l1.31 6.56h1.37m14.98 4.22V17H8.25v2.5m12.5-3.75v-3.12H12v3.12m8.75-4.37V8.25H12v3.13M20.75 7V4.5H8.25V7Z"/>
                </svg>
                Word Document
              </button>
              <button @click="exportAs('excel')" class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 48 48">
                  <path fill-rule="evenodd" d="M29 6v8h13v2H29v7h13V8c0-1.105-.836-2-1.867-2zm0 19h13v7H29zm0 9h13v6c0 1.105-.836 2-1.867 2H29zm-2 0v8H15.867C14.836 42 14 41.105 14 40v-6zm0-20H14V8c0-1.105.836-2 1.867-2H27zm-3.948 2v7H27v-7zm0 9v7H27v-7zM6 17a1 1 0 0 1 1-1h13.158a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm3.607 2h2.26l1.834 3.754L15.64 19h2.112l-2.91 5l2.976 5H15.59l-1.999-3.93l-1.99 3.93H9.34l3.024-5.018z" clip-rule="evenodd"/>
                </svg>
                Spreadsheet
              </button>
            </div>
          </div>
        </div>

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