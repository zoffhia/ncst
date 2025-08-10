const { createApp } = Vue;

// Admin Login App
if (document.getElementById('adminLoginApp')) {
    createApp({
        data() {
            return {
                email: '',
                password: '',
                terms: false,
                loading: false,
                showPassword: false
            }
        },
        methods: {
            async handleLogin() {
                event.preventDefault();

                try {
                    const formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('email', this.email);
                    formData.append('password', this.password);
                    formData.append('user_type', 'admin');

                    const response = await fetch('/ncst/functions/saving_session.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            text: result.message,
                            confirmButtonColor: '#1d4ed8',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.replace(result.redirect);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: result.message,
                            confirmButtonColor: '#1d4ed8'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to connect to server. Please try again.',
                        confirmButtonColor: '#1d4ed8'
                    });
                } finally {
                    this.loading = false;
                }
            },
            checkTerms() {
                this.terms = true;
                document.getElementById('formTerms').checked = true;
            },
            togglePassword() {
                this.showPassword = !this.showPassword;
            }
        }
    }).mount('#adminLoginApp');
}

// Employee Login App
if (document.getElementById('employeeLoginApp')) {
    createApp({
        data() {
            return {
                email: '',
                password: '',
                terms: false,
                loading: false
            }
        },
        methods: {
            async handleLogin() {
                event.preventDefault();

                try {
                    const formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('email', this.email);
                    formData.append('password', this.password);
                    formData.append('user_type', 'employee');

                    const response = await fetch('/ncst/functions/saving_session.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            text: result.message,
                            confirmButtonColor: '#1d4ed8',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.replace(result.redirect);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: result.message,
                            confirmButtonColor: '#1d4ed8'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to connect to server. Please try again.',
                        confirmButtonColor: '#1d4ed8'
                    });
                } finally {
                    this.loading = false;
                }
            },
            checkTerms() {
                this.terms = true;
            }
        }
    }).mount('#employeeLoginApp');
}

// Student Login App
if (document.getElementById('studentLoginApp')) {
    createApp({
        data() {
            return {
                studentNo: '',
                password: '',
                terms: false,
                loading: false,
                message: '',
                messageType: ''
            }
        },
      
        methods: {
            async handleLogin() {
                event.preventDefault();
                console.log('Student login attempt started');
                
                if (!this.studentNo || !this.password) {
                    this.message = 'Please fill in all fields';
                    this.messageType = 'error';
                    console.log('Validation failed: missing fields');
                    return;
                }
                
                if (!this.terms) {
                    this.message = 'Please agree to the terms and conditions';
                    this.messageType = 'error';
                    console.log('Validation failed: terms not agreed');
                    return;
                }
          
                this.loading = true;
                console.log('Sending login request for student:', this.studentNo);
          
                try {
                    const formData = new FormData();
                    formData.append('action', 'login');
                    formData.append('studentNo', this.studentNo);
                    formData.append('password', this.password);
                    formData.append('user_type', 'student');

                    console.log('Form data prepared:', {
                        action: 'login',
                        studentNo: this.studentNo,
                        password: this.password,
                        user_type: 'student'
                    });

                    const response = await fetch('/ncst/functions/saving_session.php', {
                        method: 'POST',
                        body: formData
                    });
            
                    console.log('Response received:', response.status);
                    const result = await response.json();
                    console.log('Response data:', result);
            
                    if (result.status === 'success') {
                        this.message = result.message;
                        this.messageType = 'success';
                        console.log('Login successful, redirecting...');
                        setTimeout(() => {
                            window.location.href = '/ncst/portals/student_portal.php';
                        }, 2000);
                    } else {
                        this.message = result.message;
                        this.messageType = 'error';
                        console.log('Login failed:', result.message);
                    }
            
                } catch (error) {
                    console.error('Login error:', error);
                    this.message = 'An error occurred during login';
                    this.messageType = 'error';
                } finally {
                    this.loading = false;
                }
            }
        }
    }).mount('#studentLoginApp');
} 