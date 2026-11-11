/**
 * Pictures are shrunk on the phone before sending (max 1600 px, JPEG): uploads stay
 * under the 4.5 MB request limit of the hosting and use less mobile data.
 */
const MAX_SIDE = 1600;

async function shrink(file) {
    if (!file.type.startsWith('image/') || file.type === 'image/gif') {
        return file;
    }

    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' }).catch(() => null);

    if (!bitmap) {
        return file;
    }

    const ratio = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));

    if (ratio === 1 && file.size < 1_500_000) {
        return file;
    }

    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * ratio);
    canvas.height = Math.round(bitmap.height * ratio);
    const context = canvas.getContext('2d');
    context.fillStyle = '#fff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.82));

    return blob ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file;
}

export default function setupImageResizing() {
    document.addEventListener('change', async (event) => {
        const input = event.target;

        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.accept.includes('image') || input.dataset.resized) {
            return;
        }

        const file = input.files?.[0];

        if (!file || typeof DataTransfer === 'undefined') {
            return;
        }

        const form = input.form;
        const submit = form?.querySelector('button:not([type="button"])');
        submit?.setAttribute('disabled', '');

        try {
            const smaller = await shrink(file);

            if (smaller !== file) {
                const transfer = new DataTransfer();
                transfer.items.add(smaller);
                input.dataset.resized = '1';
                input.files = transfer.files;
                delete input.dataset.resized;
            }
        } finally {
            submit?.removeAttribute('disabled');
        }
    });
}
