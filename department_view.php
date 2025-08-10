<?php include('includes/admin_header.php'); ?>

<!-- Main content wrapper -->
<div class="bg-gray-100 px-2 sm:px-4 md:px-6 py-6 md:ml-64 mt-20 min-h-screen" id="departmentViewApp">
    <!-- Selection for Department Type -->
    <div class="w-full max-w-lg sm:max-w-2xl md:max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-4 sm:p-6">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-950">Select Department Type</h1>
        <div class="flex flex-col sm:flex-row justify-center mt-6 gap-4 sm:gap-6">
            <button @click="selectDepartmentType('academic')" class="flex flex-col items-center bg-blue-700 hover:bg-blue-800 text-sm text-white px-4 py-2 rounded w-full sm:w-32">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-1" width="42" height="42" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 3L1 9l11 6l9-4.91V17h2V9M5 13.18v4L12 21l7-3.82v-4L12 17z"/>
                </svg>
                Academic
            </button>
            <button @click="selectDepartmentType('non-academic')" class="flex flex-col items-center bg-green-700 hover:bg-green-800 text-white px-4 text-sm py-2 rounded w-full sm:w-32">
                <!-- Non-Academic Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-1" width="42" height="42" viewBox="0 0 48 48">
                    <defs>
                        <mask id="ipSBuildingThree0">
                            <g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
                            <path fill="#fff" fill-rule="evenodd" stroke="#fff" d="m24 8l20 13v23H4V21z" clip-rule="evenodd"/>
                            <path stroke="#000" d="M20 44V23l-8 5v16m16 0V23l8 5v16"/>
                            <path stroke="#fff" d="M41 44H8"/>
                            </g>
                        </mask>
                    </defs>
                    <path fill="currentColor" d="M0 0h48v48H0z" mask="url(#ipSBuildingThree0)"/>
                </svg>
                Non-Academic
            </button>
        </div>
    </div>

    <!-- Departments List -->
    <div v-show="showDepartments" class="mt-6">
        <div class="w-full max-w-2xl sm:max-w-3xl md:max-w-6xl mx-auto bg-white shadow-lg rounded-lg p-4 sm:p-6">
            <h2 class="text-xl font-bold mb-4">{{ departmentTitle }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 sm:px-6 py-3">Department Name</th>
                            <th class="px-4 sm:px-6 py-3">Type</th> <!--Academic or Non-Academic-->    
                            <th class="px-4 sm:px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(dept, index) in departments" :key="index" class="border-b hover:bg-gray-50">
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ dept.name }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ dept.type }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3 space-x-2">
                                <button v-if="currentDeptType === 'academic'" @click="viewPrograms()" class='px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800'>View Programs</button>
                                <button v-if="currentDeptType === 'academic'" @click="editAcademicDepartment(index)" class='px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600'>Edit</button>
                                <button v-if="currentDeptType === 'non-academic'" @click="viewEmployees(dept.name)" class='px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800'>View Employees</button>
                                <button v-if="currentDeptType === 'non-academic'" @click="editNonAcademicDepartment(index)" class='px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600'>Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!--Dynamic Modal-->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-blue-900/60">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xs sm:max-w-lg md:max-w-2xl lg:max-w-4xl max-h-[90vh] overflow-y-auto p-2 sm:p-4 md:p-6 relative flex flex-col">
            <button @click="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">{{ modalTitle }}</h2>
            
            <!-- Loading State -->
            <div v-if="modalType === 'loading'" class="text-center">
                <p>Loading...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="modalType === 'error'" class="text-red-600">
                <p>{{ errorMessage }}</p>
            </div>

            <!-- Form State -->
            <div v-else-if="modalType === 'form'" class="w-full">
                <!-- Academic Department Edit Form -->
                <form v-if="editingType === 'academic'" @submit.prevent="saveAcademicDeptEdit()">
                    <div class='mb-4'>
                        <label class='block mb-1 font-semibold'>Department Name</label>
                        <input type='text' v-model="academicDepartments[editingIndex].name" class='w-full border px-3 py-2 rounded' required />
                    </div>
                    <div class='flex flex-col sm:flex-row gap-2'>
                        <button type='submit' class='px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800'>Save</button>
                        <button type='button' @click='closeModal()' class='px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400'>Cancel</button>
                    </div>
                </form>

                <!-- Non-Academic Department Edit Form -->
                <form v-else-if="editingType === 'non-academic'" @submit.prevent="saveNonAcademicDeptEdit()">
                    <div class='mb-4'>
                        <label class='block mb-1 font-semibold'>Department Name</label>
                        <input type='text' v-model="nonAcademicDepartments[editingIndex].name" class='w-full border px-3 py-2 rounded' required />
                    </div>
                    <div class='flex flex-col sm:flex-row gap-2'>
                        <button type='submit' class='px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800'>Save</button>
                        <button type='button' @click='closeModal()' class='px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400'>Cancel</button>
                    </div>
                </form>

                <!-- Program Edit Form -->
                <form v-else-if="editingType === 'program'" @submit.prevent="saveProgramEdit()">
                    <div class='mb-4'>
                        <label class='block mb-1 font-semibold'>Program Name</label>
                        <input type='text' v-model="academicPrograms[editingIndex].name" class='w-full border px-3 py-2 rounded' required />
                    </div>
                    <div class='mb-4'>
                        <label class='block mb-1 font-semibold'>Code</label>
                        <input type='text' v-model="academicPrograms[editingIndex].code" class='w-full border px-3 py-2 rounded' required />
                    </div>
                    <div class='mb-4'>
                        <label class='block mb-1 font-semibold'>Level</label>
                        <select v-model="academicPrograms[editingIndex].level" class='w-full border px-3 py-2 rounded'>
                            <option value='Undergraduate'>Undergraduate</option>
                            <option value='Graduate'>Graduate</option>
                        </select>
                    </div>
                    <div class='flex flex-col sm:flex-row gap-2'>
                        <button type='submit' class='px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800'>Save</button>
                        <button type='button' @click='closeModal()' class='px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400'>Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Programs Table -->
            <div v-else-if="modalType === 'programs-table'" class='overflow-x-auto'>
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Program Name</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Code</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Level</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prog, index) in academicPrograms" :key="index" class="border-b hover:bg-gray-50">
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ prog.name }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ prog.code }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ prog.level }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3 space-x-2">
                                <button @click="viewEmployees(prog.name)" class='px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800'>View Employees</button>
                                <button @click="editProgram(index)" class='px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600'>Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Employees Table -->
            <div v-else-if="modalType === 'employees-table'" class='overflow-x-auto'>
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Name</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Role</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Department</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Email</th>
                            <th class="px-2 sm:px-4 md:px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(emp, idx) in currentEmployees" :key="idx" class="border-b hover:bg-gray-50">
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ emp.fullName }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ emp.role }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ emp.department }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ emp.email }}</td>
                            <td class="px-2 sm:px-4 md:px-6 py-3">{{ emp.status }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/ncst/js/vue.global.js"></script>
<script src="/ncst/js/department_view_vue.js"></script>
<?php include('includes/footer.php'); ?>
