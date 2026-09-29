export interface Seksi {
    id: number;
    nama: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'Administrator' | 'Staff';
    status: 'Aktif' | 'Tidak Aktif';
    photo?: string | null;
    seksi_id?: number | null;
    seksi?: Seksi | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    flash: {
        success?: string;
        error?: string;
    };
};
