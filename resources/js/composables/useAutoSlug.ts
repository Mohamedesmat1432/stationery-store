export function useAutoSlug(form: any, sourceField: string = 'name', targetField: string = 'slug', isEdit: boolean = false) {

    const generateSlug = (text: string) => {
        return text
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const autoSlug = () => {
        if (!isEdit && form[sourceField]) {
            form[targetField] = generateSlug(form[sourceField]);
        }
    };

    return {
        autoSlug,
        generateSlug,
    };
}
