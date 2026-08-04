import { useCallback, useState } from 'react';

export default function LeaveAttachmentUpload({
    application,
    attachments = [],
    policyDetail = null,
    onUpload,
    onRemove,
    onComplete,
    processing = false,
}) {
    const [dragActive, setDragActive] = useState(false);
    const [pendingFiles, setPendingFiles] = useState([]);

    const isRequired = policyDetail?.attachment_required ?? false;
    const requiredAfterDays = policyDetail?.attachment_required_after_days ?? null;
    const maxFiles = policyDetail?.maximum_attachment_files ?? 1;
    const maxSizeMB = policyDetail?.maximum_attachment_size_mb ?? 5;
    const allowedExtensions = policyDetail?.allowed_extensions
        ? policyDetail.allowed_extensions.split(',').map(ext => ext.trim().toLowerCase())
        : ['pdf', 'jpg', 'jpeg', 'png'];

    const validateFile = useCallback((file) => {
        const extension = file.name.split('.').pop()?.toLowerCase();

        if (!allowedExtensions.includes(extension)) {
            return `Invalid file type. Allowed: ${allowedExtensions.join(', ')}`;
        }

        if (file.size > maxSizeMB * 1024 * 1024) {
            return `File size exceeds maximum limit of ${maxSizeMB}MB.`;
        }

        return null;
    }, [allowedExtensions, maxSizeMB]);

    const handleDrag = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    }, []);

    const handleDrop = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            const file = e.dataTransfer.files[0];
            const error = validateFile(file);

            if (error) {
                alert(error);
                return;
            }

            if (pendingFiles.length + attachments.length >= maxFiles) {
                alert(`Maximum ${maxFiles} file(s) allowed.`);
                return;
            }

            setPendingFiles((prev) => [...prev, file]);
        }
    }, [attachments.length, maxFiles, pendingFiles.length, validateFile]);

    const handleChange = useCallback((e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const error = validateFile(file);

            if (error) {
                alert(error);
                return;
            }

            if (pendingFiles.length + attachments.length >= maxFiles) {
                alert(`Maximum ${maxFiles} file(s) allowed.`);
                return;
            }

            setPendingFiles((prev) => [...prev, file]);
            e.target.value = '';
        }
    }, [attachments.length, maxFiles, pendingFiles.length, validateFile]);

    const removePendingFile = (index) => {
        setPendingFiles((prev) => prev.filter((_, i) => i !== index));
    };

    const uploadPendingFiles = async () => {
        for (const file of pendingFiles) {
            try {
                await onUpload?.(file);
            } catch (error) {
                alert(error.message || 'Upload failed. Please try again.');
                return;
            }
        }
        setPendingFiles([]);
        onComplete?.();
    };

    const getHelperMessage = () => {
        if (!isRequired) {
            return 'No supporting document is required.';
        }

        let message = `Attachment is required. Allowed types: ${allowedExtensions.join(', ')}. Max size: ${maxSizeMB}MB.`;
        if (requiredAfterDays !== null) {
            message += ` Required if requested days >= ${requiredAfterDays}.`;
        }
        if (maxFiles > 1) {
            message += ` Maximum ${maxFiles} files allowed.`;
        }

        return message;
    };

    const canUploadMore = pendingFiles.length + attachments.length < maxFiles;

    return (
        <div className="space-y-3">
            <div
                onDragEnter={handleDrag}
                onDragLeave={handleDrag}
                onDragOver={handleDrag}
                onDrop={handleDrop}
                className={`relative rounded-xl border-2 border-dashed p-6 text-center transition-colors ${
                    dragActive
                        ? 'border-sky-500 bg-sky-50'
                        : 'border-slate-300 bg-slate-50 hover:border-sky-400'
                } ${!canUploadMore ? 'opacity-50 pointer-events-none' : ''}`}
            >
                <input
                    type="file"
                    id="file-upload"
                    className="hidden"
                    onChange={handleChange}
                    accept={allowedExtensions.map(ext => `.${ext}`).join(',')}
                    disabled={!canUploadMore || processing}
                />
                <label htmlFor="file-upload" className="cursor-pointer">
                    <svg className="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v12m0 0v4m0-4H32m-4 12H20m-4-12v4m0-4v4m0-4H28" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    <p className="mt-2 text-sm text-slate-600">
                        <span className="font-semibold text-sky-700">Click to upload</span> or drag and drop
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                        {allowedExtensions.join(', ').toUpperCase()} up to {maxSizeMB}MB
                    </p>
                </label>
            </div>

            <p className="text-xs text-slate-500">{getHelperMessage()}</p>

            {pendingFiles.length > 0 && (
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <h4 className="text-sm font-medium text-slate-700">Selected Files ({pendingFiles.length})</h4>
                        <button
                            type="button"
                            onClick={uploadPendingFiles}
                            disabled={processing}
                            className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                        >
                            {processing ? 'Uploading...' : 'Upload Files'}
                        </button>
                    </div>
                    <ul className="space-y-2">
                        {pendingFiles.map((file, index) => (
                            <li key={index} className="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3">
                                <div className="flex items-center gap-3">
                                    {file.type.startsWith('image/') ? (
                                        <img src={URL.createObjectURL(file)} alt={file.name} className="h-10 w-10 rounded object-cover" />
                                    ) : (
                                        <div className="flex h-10 w-10 items-center justify-center rounded bg-slate-100">
                                            <svg className="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    )}
                                    <div>
                                        <p className="text-sm font-medium text-slate-900">{file.name}</p>
                                        <p className="text-xs text-slate-500">{(file.size / 1024).toFixed(1)} KB</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => removePendingFile(index)}
                                    className="text-red-600 hover:text-red-800"
                                >
                                    Remove
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {attachments.length > 0 && (
                <div className="space-y-2">
                    <h4 className="text-sm font-medium text-slate-700">Uploaded Attachments ({attachments.length})</h4>
                    <ul className="space-y-2">
                        {attachments.map((attachment) => (
                            <li key={attachment.id} className="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3">
                                <div className="flex items-center gap-3">
                                    <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${
                                        attachment.status === 'verified' ? 'bg-green-100 text-green-700' :
                                        attachment.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                        'bg-yellow-100 text-yellow-700'
                                    }`}>
                                        {attachment.status}
                                    </span>
                                    <a href={route('employee.leave.applications.attachments.download', [application.id, attachment.id])} className="text-sm font-medium text-sky-700 hover:text-sky-900">
                                        {attachment.original_file_name || attachment.file_name}
                                    </a>
                                    <span className="text-xs text-slate-500">
                                        {(attachment.file_size / 1024).toFixed(1)} KB
                                    </span>
                                </div>
                                {attachment.status === 'pending' && (
                                    <button
                                        type="button"
                                        onClick={() => onRemove?.(attachment.id)}
                                        className="text-red-600 hover:text-red-800"
                                    >
                                        Remove
                                    </button>
                                )}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
