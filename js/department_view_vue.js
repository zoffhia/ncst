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
            academicDepartments: [],
            nonAcademicDepartments: [],
            academicPrograms: [],
            currentEmployees: [],
            loading: false,
            message: '',
            messageType: '',
            editingIndex: -1,
            editingType: '', // 'academic', 'non-academic', 'program'
            errorMessage: '',
            selectedDepartment: null // To store the currently selected department for programs view
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

    mounted() {
        this.loadDepartments('academic');
    },
    
    methods: {
        async loadDepartments(type) {
            try {
                const formData = new FormData();
                formData.append('action', 'get_departments_by_type');
                formData.append('type', type);
                
                // Add timestamp to prevent caching
                const response = await fetch(`/ncst/functions/department_functions.php?t=${new Date().getTime()}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    if (type === 'academic') {
                        this.academicDepartments = data.data;
                    } else {
                        this.nonAcademicDepartments = data.data;
                    }
                } else {
                    console.error('Error loading departments:', data.message);
                }
            } catch (error) {
                console.error('Error loading departments:', error);
            }
        },
        
        async loadPrograms(departmentId) {
            try {
                const formData = new FormData();
                formData.append('action', 'get_programs_by_department');
                formData.append('department_id', departmentId);

                const response = await fetch(`/ncst/functions/department_functions.php?t=${new Date().getTime()}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.academicPrograms = data.data;
                } else {
                    console.error('Error loading programs:', data.message);
                }
            } catch (error) {
                console.error('Error loading programs:', error);
            }
        },
        
        selectDepartmentType(type) {
            this.currentDeptType = type;
            this.showDepartments = true;
            this.loadDepartments(type);
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
        
        addAcademicDepartment() {
            this.academicDepartments.push({ name: '', type: 'academic' });
            this.editingIndex = this.academicDepartments.length - 1;
            this.editingType = 'academic';
            this.openModal('Add Academic Department', 'form');
        },
        
        async saveAcademicDeptEdit() {
            try {
                const department = this.academicDepartments[this.editingIndex];
                const formData = new FormData();

                if (department.id) {
                    formData.append('action', 'update_department');
                    formData.append('id', department.id);
                } else {
                    formData.append('action', 'add_department');
                }
                
                formData.append('name', department.name);
                formData.append('type', 'academic');
                
                const response = await fetch('/ncst/functions/department_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.loadDepartments('academic');
                } else {
                    console.error('Error saving department:', data.message);
                }
            } catch (error) {
                console.error('Error saving department:', error);
            }
            
            this.closeModal();
        },

        editNonAcademicDepartment(index) {
            this.editingIndex = index;
            this.editingType = 'non-academic';
            this.openModal('Edit Non-Academic Department', 'form');
        },
        
        addNonAcademicDepartment() {
            this.nonAcademicDepartments.push({ name: '', type: 'non-academic' });
            this.editingIndex = this.nonAcademicDepartments.length - 1;
            this.editingType = 'non-academic';
            this.openModal('Add Non-Academic Department', 'form');
        },
        
        async saveNonAcademicDeptEdit() {
            try {
                const department = this.nonAcademicDepartments[this.editingIndex];
                const formData = new FormData();

                if (department.id) {
                    formData.append('action', 'update_department');
                    formData.append('id', department.id);
                } else {
                    formData.append('action', 'add_department');
                }
                
                formData.append('name', department.name);
                formData.append('type', 'non-academic');
                
                const response = await fetch('/ncst/functions/department_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.loadDepartments('non-academic');
                } else {
                    console.error('Error saving department:', data.message);
                }
            } catch (error) {
                console.error('Error saving department:', error);
            }
            
            this.closeModal();
        },

        viewPrograms(department) {
            this.selectedDepartment = department;
            this.loadPrograms(department.id);
            this.openModal('Programs', 'programs-table');
        },

        editProgram(index) {
            this.editingIndex = index;
            this.editingType = 'program';
            this.openModal('Edit Program', 'form');
        },
        
        addProgram() {
            this.academicPrograms.push({
                name: '',
                code: '',
                level: 'Undergraduate',
                department_id: this.selectedDepartment.id
            });
            this.editingIndex = this.academicPrograms.length - 1;
            this.editingType = 'program';
            this.openModal('Add Program', 'form');
        },

        async saveProgramEdit() {
            try {
                const program = this.academicPrograms[this.editingIndex];
                const formData = new FormData();

                if (program.id) {
                    formData.append('action', 'update_program');
                    formData.append('id', program.id);
                } else {
                    formData.append('action', 'add_program');
                }
                
                formData.append('department_id', program.department_id);
                formData.append('name', program.name);
                formData.append('code', program.code);
                formData.append('level', program.level);
                
                const response = await fetch('/ncst/functions/department_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.loadPrograms(program.department_id);
                } else {
                    console.error('Error saving program:', data.message);
                }
            } catch (error) {
                console.error('Error saving program:', error);
            }
            
            this.closeModal();
        },

        async viewEmployees(departmentName) {
            this.selectedDepartment = { name: departmentName };
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