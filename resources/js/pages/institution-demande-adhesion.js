function deleteDocument(documentId) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }

    const form = document.getElementById('delete-document-form');
    form.action = `/demandedocument/${documentId}`;
    form.submit();
}

window.deleteDocument = deleteDocument;

function bootPage() {
        // Dependents Section Script
        document.addEventListener('DOMContentLoaded', function () {

            const container = document.getElementById('dependents-container');
            const messageDiv = document.getElementById('no-dependents-message');

            // ✅ FIX: count what already exists in DOM
            let dependentCount = container.querySelectorAll('.dependent-entry').length;

            // Event delegation for delete
            container.addEventListener('click', function (e) {
                const deleteBtn = e.target.closest('[data-action="delete-dependent"]');
                if (deleteBtn) {
                    deleteDependent(deleteBtn);
                }
            });

            function addDependent() {
                const template = document.getElementById('dependentTemplate');
                const newIndex = dependentCount;

                const newEntry = document.importNode(template.content, true);

                newEntry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/__index__/g, newIndex);
                });

                container.insertBefore(newEntry, messageDiv);
                dependentCount++;
                messageDiv.classList.add('hidden');
            }

            function deleteDependent(button) {
                const entry = button.closest('.dependent-entry');
                if (!entry) return;

                entry.remove();

                // Reindex remaining entries
                const entries = container.querySelectorAll('.dependent-entry');
                entries.forEach((entry, index) => {
                    entry.querySelectorAll('[name]').forEach(el => {
                        el.name = el.name.replace(/dependents\[\d+]/g, `dependents[${index}]`);
                    });
                });

                dependentCount = entries.length;
                messageDiv.classList.toggle('hidden', dependentCount > 0);
            }

            document.getElementById('add-dependent')
                .addEventListener('click', addDependent);
        });

        // Previous Jobs Section Script
        document.addEventListener('DOMContentLoaded', function () {

            const container = document.getElementById('previous-jobs-container');
            const messageDiv = document.getElementById('no-jobs-message');
            const addBtn = document.getElementById('add-job');

            // ✅ FIX: count existing jobs in DOM (old + DB)
            let jobIndex = container.querySelectorAll('.previous-job').length;

            // Event delegation for delete
            container.addEventListener('click', function (e) {
                const deleteBtn = e.target.closest('[data-action="delete-job"]');
                if (deleteBtn) {
                    deleteJob(deleteBtn);
                }
            });

            function addJob() {
                const template = document.getElementById('jobTemplate');

                const newEntry = document.importNode(template.content, true);

                newEntry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/__index__/g, jobIndex);
                });

                container.insertBefore(newEntry, messageDiv);
                jobIndex++;
                messageDiv.classList.add('hidden');
            }

            function deleteJob(button) {
                const entry = button.closest('.previous-job');
                if (!entry) return;

                entry.remove();

                // Reindex remaining jobs
                const entries = container.querySelectorAll('.previous-job');
                entries.forEach((entry, index) => {
                    entry.querySelectorAll('[name]').forEach(el => {
                        el.name = el.name.replace(
                            /previous_jobs\[\d+]/g,
                            `previous_jobs[${index}]`
                        );
                    });
                });

                jobIndex = entries.length;
                messageDiv.classList.toggle('hidden', jobIndex > 0);
            }

            addBtn.addEventListener('click', addJob);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPage);
} else {
    bootPage();
}
