const { createApp } = Vue;

createApp({
    data() {
        return {
            showModal: false,
            users: [],
            loading: false,
            userForm: {
                userType: '',
                role: '',
                firstName: '',
                midName: '',
                lastName: '',
                suffix: '',
                birthDate: '',
                email: '',
                password: '',
                adminID: '',
                empId: '',
                department: ''
            },
            roleOptions: {
                admin: ['admin'],
                employee: ['registrar', 'treasury', 'department head', 'records']
            },
            departmentOptions: {
                'registrar': ['Registrar Office'],
                'treasury': ['Treasury Office'],
                'department head': ['Architecture and Engineering', 'Computer Science Department'],
                'custodian': ['Records Office'],
                'records': ['Records Office'],
                'admin': ['Administration Office']
            },
            message: '',
            messageType: ''
        }
    },
  
    mounted() {
        this.loadUsers();
    },
  
    computed: {
        availableRoles() {
            return this.userForm.userType ? this.roleOptions[this.userForm.userType] : [];
        },
    
        availableDepartments() {
            return this.userForm.role ? this.departmentOptions[this.userForm.role] || [] : [];
        },
    
        showEmployeeFields() {
            return ['registrar', 'treasury', 'clinic nurse', 'department head', 'custodian', 'records'].includes(this.userForm.role);
        },
    
        showAdminFields() {
            return this.userForm.role === 'admin';
        }
    },
  
    methods: {
        updateRoles() {
            this.userForm.role = '';
            this.userForm.department = '';
        },
        
        updateDepartments() {
            this.userForm.department = '';
        },
        
        async loadUsers() {
            this.loading = true;
      
            try {
                const formData = new FormData();
                formData.append('action', 'get_all_users');
        
                const response = await fetch('/ncst/functions/user_count_functions.php', {
                    method: 'POST',
                    body: formData
                });
        
                const data = await response.json();
                console.log('Response from server:', data);
        
                if (data.status === 'success') {
                    this.users = data.users;
                    console.log('Users loaded:', this.users);
                } else {
                    console.error('Server returned error:', data.message || 'Unknown error');
                }
        
            } catch (error) {
                console.error('Error loading users:', error);
            } finally {
                this.loading = false;
            }
        },
    
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString();
        },
    
        capitalizeRole(role) {
            if (!role) return '';
            return role.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        },
    
        openModal() {
            this.showModal = true;
            this.resetForm();
        },
    
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
    
        resetForm() {
            this.userForm = {
                userType: '',
                role: '',
                firstName: '',
                midName: '',
                lastName: '',
                suffix: '',
                birthDate: '',
                email: '',
                password: '',
                adminID: '',
                empId: '',
                department: ''
            };
            this.message = '';
            this.messageType = '';
        },
    
        async addUser() {
            if (!this.validateForm()) {
                return;
            }
      
            this.loading = true;
      
            try {
                const formData = new FormData();
                formData.append('action', 'add_user');
                formData.append('userType', this.userForm.userType);
                formData.append('role', this.userForm.role);
                formData.append('firstName', this.userForm.firstName);
                formData.append('midName', this.userForm.midName);
                formData.append('lastName', this.userForm.lastName);
                formData.append('suffix', this.userForm.suffix);
                formData.append('birthDate', this.userForm.birthDate);
                formData.append('email', this.userForm.email);
                formData.append('password', this.userForm.password);
                formData.append('department', this.userForm.department);
        
                // Add dynamic fields based on role
                if (this.showEmployeeFields) {
                    formData.append('empID', this.userForm.empId);
                } else if (this.showAdminFields) {
                    formData.append('adminID', this.userForm.adminID);
                }
        
                const response = await fetch('/ncst/functions/user_count_functions.php', {
                    method: 'POST',
                    body: formData
                });
        
                const data = await response.json();
        
                if (data.status === 'success') {
                    this.message = data.message;
                    this.messageType = 'success';
                    await this.loadUsers();
                    setTimeout(() => {
                        this.closeModal();
                    }, 2000);
                } else {
                    this.message = 'Error: ' + data.message;
                    this.messageType = 'error';
                }
        
            } catch (error) {
                this.message = 'An error occurred while adding the user.';
                this.messageType = 'error';
            } finally {
                this.loading = false;
            }
        },
    
        validateForm() {
            if (!this.userForm.userType) {
                this.message = 'Please select a user type.';
                this.messageType = 'error';
                return false;
            }
      
            if (!this.userForm.role) {
                this.message = 'Please select a role.';
                this.messageType = 'error';
                return false;
            }
      
            if (!this.userForm.firstName || !this.userForm.lastName || !this.userForm.email || !this.userForm.password) {
                this.message = 'Please fill in all required fields.';
                this.messageType = 'error';
                return false;
            }
      
            if (!this.userForm.department) {
                this.message = 'Please select a department.';
                this.messageType = 'error';
                return false;
            }
      
            if (this.showEmployeeFields && !this.userForm.empId) {
                this.message = 'Please fill in the employee ID.';
                this.messageType = 'error';
                return false;
            }
      
            if (this.showAdminFields && !this.userForm.adminID) {
                this.message = 'Please fill in the admin ID.';
                this.messageType = 'error';
                return false;
            }
      
            return true;
        },
    
        toggleStatus() {
            const btn = document.getElementById("statusBtn");
            const isActive = btn.textContent === "Activate";
            btn.textContent = isActive ? "Deactivate" : "Activate";
            btn.className = isActive 
                ? "bg-gray-500 text-white px-3 py-1 rounded"
                : "bg-green-500 text-white px-3 py-1 rounded";
        },

        editUser(user) {
            // Populate the form with user data
            this.userForm = {
                userType: user.user_type,
                role: user.role,
                firstName: user.full_name.split(' ')[0] || '',
                midName: user.full_name.split(' ')[1] || '',
                lastName: user.full_name.split(' ')[2] || '',
                suffix: user.full_name.split(' ')[3] || '',
                birthDate: user.birthDate,
                email: user.email,
                password: '', // Don't populate password for security
                adminID: user.user_type === 'admin' ? user.id_no : '',
                empId: user.user_type === 'employee' ? user.id_no : '',
                department: user.department
            };
            
            this.showModal = true;
        },

        async toggleUserStatus(user) {
            const action = user.status === 'Active' ? 'deactivate' : 'activate';
            const confirmMessage = `Are you sure you want to ${action} ${user.full_name}?`;
            
            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'toggle_user_status');
                formData.append('userType', user.user_type);
                formData.append('userId', user.id_no);
                formData.append('newStatus', user.status === 'Active' ? 'Inactive' : 'Active');

                const response = await fetch('/ncst/functions/user_count_functions.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Reload users to reflect the change
                    await this.loadUsers();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while updating user status.');
            }
        }
    }
}).mount('#userManagementApp');