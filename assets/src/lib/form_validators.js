// validation functions
export const required = value => (value == null || value == '' ? 'Required' : undefined);

export const notEmpty = value => (value == '' ? 'Required' : undefined);

export const email = value => (value && !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(value) ? 'Invalid email' : undefined);
