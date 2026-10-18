function deleteDocument(documentId) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }

    const deleteForm = document.getElementById('delete-document-form');
    deleteForm.action = `/demandedocument/${documentId}`;
    deleteForm.submit();
}

window.deleteDocument = deleteDocument;
