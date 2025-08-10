const { createApp } = Vue;

createApp({
    data() {
        return {
            students: [],
            loading: false,
            showModal: false,
            showQueueModal: false,
            selectedStudent: null,
            studentInfo: null,
            requirements: {
                reportCard: false,
                form138Copy: false,
                goodMoral: false,
                idPicture: false
            },
            queue: [],
            showExportDropdown: false,
            message: '',
            messageType: ''
        }
    },

    mounted() {
        console.log('Vue app mounted, showModal:', this.showModal);
        this.loadStudents();
    },

    computed: {
        allRequirementsChecked() {
            return Object.values(this.requirements).every(req => req);
        },

        filteredStudents() {
            return this.students;
        }
    },

    methods: {
        async loadStudents() {
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('action', 'get_all_student_registrations');
                
                const response = await fetch('/ncst/functions/admission_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.students = data.data || [];
                } else {
                    this.students = [];
                    this.showMessage('Error loading students: ' + data.message, 'error');
                }
            } catch (error) {
                this.students = [];
                this.showMessage('Error loading students', 'error');
            } finally {
                this.loading = false;
            }
        },

        async openModal(studentID) {
            console.log('Opening modal for student:', studentID);
            this.resetRequirements();
            this.showModal = true;
            this.selectedStudent = studentID;
            
            if (studentID) {
                await this.fetchStudentDetails(studentID);
            }
        },

        closeModal() {
            console.log('Closing modal');
            this.showModal = false;
            this.selectedStudent = null;
            this.studentInfo = null;
            this.resetRequirements();
        },

        resetRequirements() {
            this.requirements = {
                reportCard: false,
                form138Copy: false,
                goodMoral: false,
                idPicture: false
            };
        },

        async fetchStudentDetails(studentID) {
            try {
                const formData = new FormData();
                formData.append('action', 'get_student_details');
                formData.append('studentID', studentID);
                
                const response = await fetch('/ncst/functions/get_student_details.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.studentInfo = data.data;
                } else {
                    this.showMessage('Error loading student details: ' + data.message, 'error');
                }
            } catch (error) {
                this.showMessage('Error loading student details', 'error');
            }
        },

        async approveStudent() {
            if (!this.selectedStudent) return;
            
            if (!this.allRequirementsChecked) {
                this.showMessage('Please check all requirements before approving', 'warning');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'approve_student');
                formData.append('studentID', this.selectedStudent);
                
                const response = await fetch('/ncst/functions/admission_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.showMessage('Student approved successfully!', 'success');
                    this.closeModal();
                    await this.loadStudents();
                } else {
                    this.showMessage('Error approving student: ' + data.message, 'error');
                }
            } catch (error) {
                this.showMessage('Error approving student', 'error');
            }
        },

        addToQueue(studentID) {
            const student = this.students.find(s => s.studentID === studentID);
            if (student && !this.queue.find(q => q.studentID === studentID)) {
                this.queue.push(student);
                this.showMessage(`${student.fullName} added to queue`, 'success');
            } else if (this.queue.find(q => q.studentID === studentID)) {
                this.showMessage('Student already in queue', 'warning');
            }
        },

        removeFromQueue(index) {
            const student = this.queue[index];
            this.queue.splice(index, 1);
            this.showMessage(`${student.fullName} removed from queue`, 'info');
        },

        openQueueModal() {
            this.showQueueModal = true;
        },

        closeQueueModal() {
            this.showQueueModal = false;
        },

        processQueue() {
            if (this.queue.length === 0) {
                this.showMessage('No students in queue to process', 'warning');
                return;
            }

            this.queue.forEach((student, index) => {
                setTimeout(() => {
                    this.generateStudentID(student);
                    if (index === this.queue.length - 1) {
                        this.queue = [];
                        this.showMessage('All IDs generated successfully!', 'success');
                        this.closeQueueModal();
                    }
                }, index * 1000);
            });

            this.showMessage('Processing queue...', 'info');
        },

        generateStudentID(student) {
            console.log('Generating ID for student:', student.fullName);

            this.createStudentRecord(student);
        },

        async createStudentRecord(student) {
            try {
                const formData = new FormData();
                formData.append('action', 'create_student_record');
                formData.append('studentID', student.studentID);
                formData.append('fullName', student.fullName);
                formData.append('email', student.email);
                formData.append('course', student.course || '');
                formData.append('yearLevel', student.yearLevel || '');
                
                const response = await fetch('/ncst/functions/admission_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.showMessage(`Student ID generated for ${student.fullName}: ${data.studentNo}`, 'success');
                } else {
                    this.showMessage(`Error generating ID for ${student.fullName}: ${data.message}`, 'error');
                }
            } catch (error) {
                this.showMessage(`Error generating ID for ${student.fullName}`, 'error');
            }
        },

        toggleExportDropdown() {
            this.showExportDropdown = !this.showExportDropdown;
        },

        exportAs(type) {
            console.log('Exporting as:', type);
            this.showMessage(`Exporting as ${type.toUpperCase()}...`, 'info');
            this.showExportDropdown = false;
        },

        showMessage(message, type = 'info') {
            this.message = message;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
                this.messageType = '';
            }, 5000);
        },

        formatDate(dateString) {
            if (!dateString) return 'Not specified';
            const date = new Date(dateString);
            return date.toLocaleDateString();
        },

        getStatusClass(status) {
            switch (status) {
                case 'Approved':
                    return 'text-green-600';
                case 'Pending':
                    return 'text-yellow-600';
                case 'Rejected':
                    return 'text-red-600';
                case 'Processed':
                    return 'text-blue-600';
                default:
                    return 'text-gray-600';
            }
        },

        async archiveStudent(studentID) {
            if (confirm('Are you sure you want to archive this student? This action cannot be undone.')) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'archive_student');
                    formData.append('studentID', studentID);
                    
                    const response = await fetch('/ncst/functions/admission_functions.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        this.showMessage('Student archived successfully!', 'success');
                        this.students = this.students.filter(student => student.studentID !== studentID);
                    } else {
                        this.showMessage('Error archiving student: ' + data.message, 'error');
                    }
                } catch (error) {
                    this.showMessage('Error archiving student', 'error');
                }
            }
        }
    }
}).mount('#admissionsApp');
