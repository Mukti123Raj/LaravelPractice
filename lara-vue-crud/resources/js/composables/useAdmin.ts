import { usePage } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
}

export function useAdmin() {
    const page = usePage();
    
    const isAdmin = (): boolean => {
        const user = page.props.auth?.user as User;
        return user?.email === 'muktirajbhusal@gmail.com';
    };

    const getCurrentUser = (): User | null => {
        return page.props.auth?.user as User || null;
    };

    return {
        isAdmin,
        getCurrentUser,
    };
}
