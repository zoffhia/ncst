const { createApp } = Vue;

createApp({
    data() {
        return {
            currentDeptType: 'academic',
            showDepartments: false,
            showModal: false,
            modalTitle: '',
            modalContent: '',
            modalType: '', // 'form', 'table', 'loading', 'error'
            academicDepartments: [
                { name: 'Architecture and Engineering', type: 'Academic' },
                { name: 'Computer Science Department', type: 'Academic' }
            ],
            nonAcademicDepartments: [
                { name: 'Registrar Office', type: 'Non-Academic' },
                { name: 'Records Office', type: 'Non-Academic' },
                { name: 'Treasury Office', type: 'Non-Academic' }
            ],
            academicPrograms: [
                { name: 'BS Computer Science', code: 'BSCS', level: 'Undergraduate' },
                { name: 'BS Civil Engineering', code: 'BSCE', level: 'Undergraduate' },
                { name: 'BS Architecture', code: 'BSARCH', level: 'Undergraduate' }
            ],
            currentEmployees: [],
            loading: false,
            message: '',
            messageType: '',
            editingIndex: -1,
            editingType: '', // 'academic', 'non-academic', 'program'
            errorMessage: ''
        }
    },

    computed: {
        departments() {
            return this.currentDeptType === 'academic' 
                ? this.academicDepartments 
                : this.nonAcademicDepartments;
        },

        departmentTitle() {
            return this.currentDeptType === 'academic' 
                ? 'Academic Departments' 
                : 'Non-Academic Departments';
        }
    },

    methods: {
        selectDepartmentType(type) {
            this.currentDeptType = type;
            this.showDepartments = true;
        },

        openModal(title, type = 'loading') {
            this.modalTitle = title;
            this.modalType = type;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.modalTitle = '';
            this.modalType = '';
            this.editingIndex = -1;
            this.editingType = '';
            this.errorMessage = '';
        },

        editAcademicDepartment(index) {
            this.editingIndex = index;
            this.editingType = 'academic';
            this.openModal('Edit Academic Department', 'form');
        },

        saveAcademicDeptEdit() {
            this.closeModal();
        },

        editNonAcademicDepartment(index) {
            this.editingIndex = index;
            this.editingType = 'non-academic';
            this.openModal('Edit Non-Academic Department', 'form');
        },

        saveNonAcademicDeptEdit() {
            this.closeModal();
        },

        viewPrograms() {
            this.openModal('Programs', 'programs-table');
        },

        editProgram(index) {
            this.editingIndex = index;
            this.editingType = 'program';
            this.openModal('Edit Program', 'form');
        },

        saveProgramEdit() {
            this.closeModal();
            this.viewPrograms();
        },

        async viewEmployees(departmentName) {
            this.loading = true;
            this.openModal('Employees', 'loading');
            
            try {
                const formData = new FormData();
                formData.append('action', 'get_all_employees');
                
                const response = await fetch('/ncst/functions/employee_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    const allEmployees = data.data;
                    
                    this.currentEmployees = allEmployees.filter(emp => 
                        emp.department === departmentName
                    );
                    
                    this.openModal('Employees', 'employees-table');
                } else {
                    this.errorMessage = data.message;
                    this.openModal('Error', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.errorMessage = 'Error loading employees. Please try again.';
                this.openModal('Error', 'error');
            } finally {
                this.loading = false;
            }
        }
    }
}).mount('#departmentViewApp');
