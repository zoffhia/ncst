const { createApp } = Vue;

window.__admissionApp__ = createApp({
    data() {
        return {
            // Student information
            student: {
                firstName: '',
                midName: '',
                lastName: '',
                suffix: '',
                address: '',
                zip: '',
                phone: '',
                gender: '0',
                civilStatus: '0',
                nationality: '0',
                birthDate: '',
                birthPlace: '',
                email: '',
                religion: '0',
                employer: '',
                position: '',
                course: '0',
                yearLevel: '0',
                houseHeroes: '0',
                nstp: '0'
            },
            
            // Educational background
            education: {
                primarySchool: '',
                primaryYear: '',
                secondarySchool: '',
                secondaryYear: '',
                tertiarySchool: '',
                tertiaryYear: '',
                courseGraduated: ''
            },
            
            // Parent/Guardian information
            parent: {
                fatherFirstName: '',
                fatherMidName: '',
                fatherLastName: '',
                fatherSuffix: '',
                fatherAddress: '',
                fatherPhone: '',
                fatherOccupation: '',
                motherFirstName: '',
                motherMidName: '',
                motherLastName: '',
                motherAddress: '',
                motherPhone: '',
                motherOccupation: '',
                guardianFirstName: '',
                guardianMidName: '',
                guardianLastName: '',
                guardianSuffix: '',
                guardianAddress: '',
                guardianPhone: '',
                guardianOccupation: '',
                guardianRelationship: ''
            },
            
            // Form state
            currentStep: 0,
            loading: false,
            message: '',
            messageType: '',
            formSubmitted: false,
            programs: []
        }
    },
    
    computed: {
        
    },
    
    methods: {
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        
        async loadPrograms() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_all_programs_with_departments');

                const response = await fetch(`/ncst/functions/department_functions.php?t=${new Date().getTime()}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.programs = data.data;
                } else {
                    console.error('Error loading programs:', data.message);
                }
            } catch (error) {
                console.error('Error loading programs:', error);
            }
        },

        nextStep() {
            if (this.currentStep < 5) {
                this.currentStep++;
                this.updateStepper();
                this.syncWithNavigation();
            } else if (this.currentStep === 5) {
                this.submitAdmission();
            }
        },
        
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.updateStepper();
                this.syncWithNavigation();
            }
        },

        updateStepper() {
            const desktopCircles = document.querySelectorAll("#desktop-stepper .step-circle");
            const mobileCircles = document.querySelectorAll("#mobile-stepper .step-circle");
            
            [desktopCircles, mobileCircles].forEach((circles) => {
                circles.forEach((circle, index) => {
                    circle.classList.remove("bg-green-200", "bg-teal-200", "bg-green-500", "bg-yellow-400", "bg-gray-300");
                    
                    if (index < this.currentStep) {
                        circle.classList.add("bg-green-500"); // completed
                    } else if (index === this.currentStep) {
                        circle.classList.add("bg-yellow-400"); // current
                    } else {
                        circle.classList.add("bg-teal-200"); // base
                    }
                });
            });
        },

        syncWithNavigation() {
            if (typeof window.currentTab !== 'undefined') {
                window.currentTab = this.currentStep;
            }
            
            const sections = document.querySelectorAll(".form-section");
            sections.forEach((section, i) => {
                if (i === this.currentStep) {
                    section.classList.remove("hidden");
                } else {
                    section.classList.add("hidden");
                }
            });

            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            
            if (prevBtn) {
                prevBtn.disabled = this.currentStep === 0;
            }
            
            if (nextBtn) {
                nextBtn.textContent = this.currentStep === sections.length - 1 ? "Submit" : "Next";
            }
        },



        async submitAdmission() {
            if (!this.student.firstName || !this.student.lastName || !this.student.address || 
                !this.student.zip || !this.student.phone || !this.student.birthDate || 
                !this.student.birthPlace || !this.student.email) {
                this.showError('Please complete the essential personal information fields (First Name, Last Name, Address, ZIP, Phone, Birth Date, Birth Place, Email).');
                return;
            }
            
            if (!this.isValidEmail(this.student.email)) {
                this.showError('Please enter a valid email address.');
                return;
            }
            
            if (!this.student.course || this.student.course === '0') {
                this.showError('Please select your desired course.');
                return;
            }
            
            if (!this.student.yearLevel || this.student.yearLevel === '0') {
                this.showError('Please select your year level.');
                return;
            }
            
            if (!this.student.houseHeroes || this.student.houseHeroes === '0') {
                this.showError('Please select a House of Heroes.');
                return;
            }
            
            if (!this.student.nstp || this.student.nstp === '0') {
                this.showError('Please select an NSTP component.');
                return;
            }
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'admission');
                formData.append('student', JSON.stringify(this.student));
                formData.append('education', JSON.stringify(this.education));
                formData.append('parent', JSON.stringify(this.parent));
                
                const response = await fetch('/ncst/functions/admission_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (error) {
                    this.showError('Server returned invalid response. Please try again.');
                    return;
                }
                
                if (result.status === 'success') {
                    this.formSubmitted = true;
                    this.clearSavedData();
                    this.showSuccess(result.message);

                    setTimeout(() => {
                        window.location.href = '/ncst/logins/student_login.php';
                    }, 3000);
                } else {
                    this.showError(result.message);
                }
                
            } catch (error) {
                this.showError('An error occurred during submission. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        showSuccess(message) {
            this.message = message;
            this.messageType = 'success';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    confirmButtonColor: '#1d4ed8'
                });
            } else {
                alert('Success: ' + message);
            }
        },

        showError(message) {
            this.message = message;
            this.messageType = 'error';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#dc2626'
                });
            } else {
                alert('Error: ' + message);
            }
        },

        resetForm() {
            this.student = {
                firstName: '',
                midName: '',
                lastName: '',
                suffix: '',
                address: '',
                zip: '',
                phone: '',
                gender: '0',
                civilStatus: '0',
                nationality: '0',
                birthDate: '',
                birthPlace: '',
                email: '',
                religion: '0',
                employer: '',
                position: '',
                course: '0',
                yearLevel: '0',
                houseHeroes: '0',
                nstp: '0'
            };
            
            this.education = {
                primarySchool: '',
                primaryYear: '',
                secondarySchool: '',
                secondaryYear: '',
                tertiarySchool: '',
                tertiaryYear: '',
                courseGraduated: ''
            };
            
            this.parent = {
                fatherFirstName: '',
                fatherMidName: '',
                fatherLastName: '',
                fatherSuffix: '',
                fatherAddress: '',
                fatherPhone: '',
                fatherOccupation: '',
                motherFirstName: '',
                motherMidName: '',
                motherLastName: '',
                motherAddress: '',
                motherPhone: '',
                motherOccupation: '',
                guardianFirstName: '',
                guardianMidName: '',
                guardianLastName: '',
                guardianSuffix: '',
                guardianAddress: '',
                guardianPhone: '',
                guardianOccupation: '',
                guardianRelationship: ''
            };
            
            this.currentStep = 0;
            this.formSubmitted = false;
            this.updateStepper();
            this.clearSavedData();
        },

        autoSave() {
            const formData = {
                student: this.student,
                education: this.education,
                parent: this.parent,
                currentStep: this.currentStep
            };
            
            localStorage.setItem('ncst_admission_form', JSON.stringify(formData));
        },

        loadSavedData() {
            const savedData = localStorage.getItem('ncst_admission_form');
            
            if (savedData) {
                try {
                    const data = JSON.parse(savedData);
                    this.student = data.student || this.student;
                    this.education = data.education || this.education;
                    this.parent = data.parent || this.parent;
                    this.currentStep = 0;
                    this.updateStepper();
                } catch (error) {
                    
                }
            }
        },
        
        // clear saved data
        clearSavedData() {
            localStorage.removeItem('ncst_admission_form');
        },

        fixAriaHiddenIssues() {
            const formContainers = document.querySelectorAll('.pt-15.overflow-x-hidden, #admissionApp, .form-section');
            formContainers.forEach(container => {
                if (container.hasAttribute('aria-hidden')) {
                    container.removeAttribute('aria-hidden');
                }
            });

            const formElements = document.querySelectorAll('input, select, textarea, button');
            formElements.forEach(element => {
                if (element.hasAttribute('aria-hidden')) {
                    element.removeAttribute('aria-hidden');
                }
            });

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                        const target = mutation.target;
                        if (target.classList.contains('pt-15') || 
                            target.id === 'admissionApp' || 
                            target.classList.contains('form-section')) {
                            target.removeAttribute('aria-hidden');
                        }
                    }
                });
            });

            const formContainer = document.querySelector('.pt-15.overflow-x-hidden');
            if (formContainer) {
                observer.observe(formContainer, {
                    attributes: true,
                    attributeFilter: ['aria-hidden']
                });
            }
        }
    },
    
    mounted() {
        this.loadSavedData();
        
        setInterval(() => {
            if (!this.formSubmitted) {
                this.autoSave();
            }
        }, 30000);

        this.currentStep = 0;
        this.syncWithNavigation();
        this.updateStepper();
        
        this.fixAriaHiddenIssues();
        this.loadPrograms();
    }
}).mount('#admissionApp');