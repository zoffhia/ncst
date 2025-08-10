const { createApp } = Vue;

createApp({
    data() {
        return {
            searchStudentNo: '',
            studentInfo: null,
            loading: false,
            hasSearched: false,
            message: '',
            messageType: ''
        }
    },

    methods: {
        async searchStudent() {
            if (!this.searchStudentNo.trim()) {
                this.showMessage('Please enter a Student Number to search', 'warning');
                return;
            }

            this.loading = true;
            this.hasSearched = true;
            this.studentInfo = null;

            try {
                const formData = new FormData();
                formData.append('action', 'search_student_by_no');
                formData.append('studentNo', this.searchStudentNo.trim());
                
                const response = await fetch('/ncst/functions/admission_functions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.studentInfo = data.data;
                    this.showMessage('Student found successfully!', 'success');
                } else {
                    this.studentInfo = null;
                    this.showMessage('No student found with the provided Student Number', 'error');
                }
            } catch (error) {
                this.studentInfo = null;
                this.showMessage('Error searching for student', 'error');
            } finally {
                this.loading = false;
            }
        },

        clearSearch() {
            this.searchStudentNo = '';
            this.studentInfo = null;
            this.hasSearched = false;
            this.message = '';
            this.messageType = '';
        },

        showMessage(message, type = 'info') {
            this.message = message;
            this.messageType = type;

            if (type === 'success') {
                setTimeout(() => {
                    this.message = '';
                    this.messageType = '';
                }, 3000);
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'Not specified';
            
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (error) {
                return 'Invalid date';
            }
        }
    }
}).mount('#studentRecordsApp');
