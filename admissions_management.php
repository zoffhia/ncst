<?php 
include('includes/registrar_header.php');
?>

<!-- Vue.js App Container -->
<div id="admissionsApp" class="p-4 md:ml-64 mt-20">
  <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Student Admission</h2>
    
    <!-- Message Display -->
    <div v-if="message" :class="{
      'bg-green-100 border-green-400 text-green-700': messageType === 'success',
      'bg-red-100 border-red-400 text-red-700': messageType === 'error',
      'bg-yellow-100 border-yellow-400 text-yellow-700': messageType === 'warning',
      'bg-blue-100 border-blue-400 text-blue-700': messageType === 'info'
    }" class="px-4 py-3 rounded relative mb-4">
      {{ message }}
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading students...</p>
    </div>
    
    <!-- Table -->
    <div v-else class="overflow-x-auto">
       <table class="w-full border-collapse border border-gray-300">
         <thead>
           <tr class="bg-blue-950 text-white">
             <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Course</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Year Level</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Date Submitted</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
             <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
           </tr>
         </thead>
         <tbody>
           <tr v-for="student in filteredStudents" :key="student.studentID" class="hover:bg-gray-50">
             <td class="border border-gray-300 px-4 py-2">{{ student.fullName }}</td>
             <td class="border border-gray-300 px-4 py-2">{{ student.email }}</td>
             <td class="border border-gray-300 px-4 py-2">{{ student.course || 'Not specified' }}</td>
             <td class="border border-gray-300 px-4 py-2">{{ student.yearLevel || 'Not specified' }}</td>
             <td class="border border-gray-300 px-4 py-2">{{ formatDate(student.dateSubmitted) }}</td>
             <td :class="getStatusClass(student.status)" class="border border-gray-300 px-4 py-2 font-semibold">
               {{ student.status }}
             </td>
             <td class="border border-gray-300 px-4 py-2">
               <button @click="openModal(student.studentID)" class="bg-blue-600 text-white px-3 py-1 rounded mr-2 hover:bg-blue-700">View</button>
               <button @click="archiveStudent(student.studentID)" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">Archive</button>
               <button v-if="student.status === 'Approved'" @click="createStudentRecord(student)" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 ml-2 rounded transition duration-200 ease-in-out">
                Generate Student No
               </button>
             </td>
           </tr>
           <tr v-if="filteredStudents.length === 0">
             <td colspan="6" class="text-center py-4 text-gray-500 border border-gray-300">No student registrations found</td>
           </tr>
         </tbody>
       </table>
    </div>
    
    <!-- Export Button -->
    <div class="p-4 md:ml-64">
      <div class="max-w-7xl mx-auto flex justify-end space-x-2">
        <div class="relative">
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
      </div>
    </div>
  </div>
  
  <!-- Student Information Modal -->
  <div v-if="showModal" class="fixed inset-0 backdrop-blur-sm bg-blue-900/60 flex items-center justify-center z-50">
  <div class="bg-white p-6 w-full max-w-4xl rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
    <h2 class="text-lg font-semibold mb-4">Student Information</h2>
    
    <!-- Loading State -->
    <div v-if="!studentInfo" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading student information...</p>
    </div>
    
    <!-- Student Information Table -->
    <div v-else>
      <!-- Personal Information Table -->
      <h4 class="font-semibold text-blue-600 mb-2">Personal Information</h4>
      <table class="w-full border-collapse border border-gray-300 mb-4">
        <tbody>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Full Name</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.firstName }} {{ studentInfo.student.midName || '' }} {{ studentInfo.student.lastName }} {{ studentInfo.student.suffix || '' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Email</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.email }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Phone</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.phone }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Address</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.address }}, {{ studentInfo.student.zip }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Birth Date</td>
            <td class="border border-gray-300 px-4 py-2">{{ formatDate(studentInfo.student.birthDate) }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Birth Place</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.birthPlace }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Gender</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.gender }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Civil Status</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.civilStatus }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Nationality</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.nationality }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Religion</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.religion }}</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Academic Information Table -->
      <h4 class="font-semibold text-blue-600 mb-2">Academic Information</h4>
      <table class="w-full border-collapse border border-gray-300 mb-4">
        <tbody>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Course</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.course || 'Not specified' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Year Level</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.yearLevel || 'Not specified' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">House of Heroes</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.houseHeroes || 'Not specified' }}</td>
          </tr>
          <tr>
            <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">NSTP Component</td>
            <td class="border border-gray-300 px-4 py-2">{{ studentInfo.student.nstp || 'Not specified' }}</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Educational Background Table -->
      <div v-if="studentInfo.education" class="mb-4">
        <h4 class="font-semibold text-blue-600 mb-2">Educational Background</h4>
        <table class="w-full border-collapse border border-gray-300 mb-4">
          <tbody>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Primary School</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.primarySchool || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Year Graduated</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.primaryYear || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Secondary School</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.secondarySchool || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Year Graduated</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.secondaryYear || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Tertiary School</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.tertiarySchool || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Year Graduated</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.education.tertiaryYear || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Course Graduated</td>
              <td class="border border-gray-300 px-4 py-2" colspan="3">{{ studentInfo.education.courseGraduated || 'Not specified' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Parent/Guardian Information Table -->
      <div v-if="studentInfo.parent" class="mb-4">
        <h4 class="font-semibold text-blue-600 mb-2">Parent/Guardian Information</h4>
        <table class="w-full border-collapse border border-gray-300 mb-4">
          <tbody>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Father's Name</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.fatherFirstName }} {{ studentInfo.parent.fatherMidName || '' }} {{ studentInfo.parent.fatherLastName }} {{ studentInfo.parent.fatherSuffix || '' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Phone</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.fatherPhone || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Father's Address</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.fatherAddress || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Occupation</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.fatherOccupation || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Mother's Name</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.motherFirstName }} {{ studentInfo.parent.motherMidName || '' }} {{ studentInfo.parent.motherLastName }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Phone</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.motherPhone || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Mother's Address</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.motherAddress || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Occupation</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.motherOccupation || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Guardian's Name</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.guardianFirstName }} {{ studentInfo.parent.guardianMidName || '' }} {{ studentInfo.parent.guardianLastName }} {{ studentInfo.parent.guardianSuffix || '' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Phone</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.guardianPhone || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Guardian's Address</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.guardianAddress || 'Not specified' }}</td>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Occupation</td>
              <td class="border border-gray-300 px-4 py-2">{{ studentInfo.parent.guardianOccupation || 'Not specified' }}</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-2 font-semibold bg-gray-50">Relationship</td>
              <td class="border border-gray-300 px-4 py-2" colspan="3">{{ studentInfo.parent.guardianRelationship || 'Not specified' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Requirements Checklist -->
      <div class="mb-4">
        <h4 class="font-semibold text-blue-600 mb-2">Requirements Checklist</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="requirements.reportCard" class="rounded">
              <span>Report Card (Form 138)</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="requirements.form138Copy" class="rounded">
              <span>Original and Photocopy of Form 138</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="requirements.goodMoral" class="rounded">
              <span>Good Moral Certificate</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="requirements.idPicture" class="rounded">
              <span>2X2 ID Picture</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 flex justify-end space-x-2">
      <button @click="approveStudent" :disabled="!allRequirementsChecked" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">Approve</button>
      <button @click="closeModal" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Close</button>
    </div>
       </div>
  </div>
  
</div>

<!-- Vue.js -->
<script src="/ncst/js/vue.global.js"></script>
<script src="/ncst/js/admissions_management_vue.js"></script>

<?php include('includes/footer.php');?>