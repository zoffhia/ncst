const { createApp } = Vue;

createApp({
    data() {
        return {
            showModal: false,
            isEditing: false,
            users: [],
            loading: false,
            dataTable: null,
            userForm: {
                userType: '',
                role: '',
                adminNo: '',
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
                employee: ['registrar', 'treasury']
            },
            departmentOptions: {
                'registrar': ['Registrar Office'],
                'treasury': ['Treasury Office']
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
            return ['registrar', 'treasury'].includes(this.userForm.role);
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
        
        initializeDataTable() {
            if (this.dataTable) {
                try {
                    this.dataTable.destroy();
                } catch (e) {
                }
            }
            this.dataTable = null;

            this.$nextTick(() => {
                setTimeout(() => {
                    const tableElement = $('#userTable');
                    if (tableElement.length > 0) {
                        try {
                            this.dataTable = tableElement.DataTable({
                                paging: true,
                                searching: true,
                                ordering: true,
                                info: true,
                                lengthChange: true,
                                pageLength: 10,
                                language: {
                                    emptyTable: "No users found.",
                                    search: "Search users:",
                                    lengthMenu: "Show _MENU_ users per page",
                                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                                    infoEmpty: "Showing 0 to 0 of 0 users",
                                    infoFiltered: "(filtered from _MAX_ total users)",
                                    paginate: {
                                        first: "First",
                                        last: "Last",
                                        next: "Next",
                                        previous: "Previous"
                                    }
                                },
                                columnDefs: [
                                    {
                                        targets: -1, // Actions column
                                        orderable: false,
                                        searchable: false
                                    }
                                ],
                                responsive: true,
                                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rtip',
                                initComplete: function() {
                                    // Add custom styling to DataTable elements
                                    $('.dataTables_filter input').addClass('border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500');
                                    $('.dataTables_length select').addClass('border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500');
                                }
                            });
                        } catch (e) {

                        }
                    } else {

                    }
                }, 100);
            });
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
        
                if (data.status === 'success') {
                    this.users = data.users;

                    setTimeout(() => {
                        this.initializeDataTable();
                    }, 100);
                } else {

                }
        
            } catch (error) {

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
            this.isEditing = false;
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
                adminNo: '',
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
            this.isEditing = false;
            this.message = '';
            this.messageType = '';
        },
    
        async addUser() {
            if (this.userForm.userType === 'admin') {
                this.userForm.role = 'admin';
                this.userForm.department = 'Admin Office';

                if (!this.userForm.password) {
                    this.userForm.password = 'ncst-admin123';
                }
            }

            if (!this.validateForm()) {
                return;
            }
      
            this.loading = true;
      
            try {

                const formData = new FormData();
                formData.append('action', 'add_user');
                formData.append('userType', this.userForm.userType);
                formData.append('role', this.userForm.role);
                formData.append('adminNo', this.userForm.adminNo);
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
                    let idMessage = '';
                    if (this.userForm.userType === 'admin' && data.adminNo) {
                        idMessage = `<br><strong>Admin No:</strong> ${data.adminNo}`;
                    } else if (this.userForm.userType === 'employee' && this.userForm.empId) {
                        idMessage = `<br><strong>Employee ID:</strong> ${this.userForm.empId}`;
                    }
                    this.message = `${data.message}${idMessage}`;
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
     
        async updateUser() {
            if (!this.validateForm()) {
                return;
            }
       
            this.loading = true;
       
            try {
                const formData = new FormData();
                formData.append('action', 'update_user');
                formData.append('userType', this.userForm.userType);
                formData.append('userId', this.userForm.userType === 'admin' ? this.userForm.adminID : this.userForm.empId);
                formData.append('role', this.userForm.role);
                formData.append('firstName', this.userForm.firstName);
                formData.append('midName', this.userForm.midName);
                formData.append('lastName', this.userForm.lastName);
                formData.append('suffix', this.userForm.suffix);
                formData.append('birthDate', this.userForm.birthDate);
                formData.append('email', this.userForm.email);
                formData.append('department', this.userForm.department);
         
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
                    }, 3000);
                } else {
                    this.message = 'Error: ' + data.message;
                    this.messageType = 'error';
                }
         
            } catch (error) {
                this.message = 'An error occurred while updating the user: ' + error.message;
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
      
            if (!this.userForm.firstName || !this.userForm.lastName || !this.userForm.email) {
                this.message = 'Please fill in all required fields.';
                this.messageType = 'error';
                return false;
            }
      
            if (!this.userForm.department) {
                this.message = 'Please select a department.';
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
            this.userForm = {
                userType: user.user_type,
                role: user.role,
                firstName: user.firstName || '',
                midName: user.midName || '',
                lastName: user.lastName || '',
                suffix: user.suffix || '',
                birthDate: user.birthDate,
                email: user.email,
                adminID: user.user_type === 'admin' ? user.id_no : '',
                empId: user.user_type === 'employee' ? user.id_no : '',
                department: user.department
            };
            
            this.isEditing = true;
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
