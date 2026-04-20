
export interface User {
    name: string;
    last_name: string;
    email: string;
    password: string;
}
export interface Login {
    email: string;
    password: string;
}

export interface Register {
    name: string;
    last_name: string;
    email: string;
    password: string;
}
export interface Blog {
    id: number;
    title: string;
    author: string | { name?: string; email?: string; id?: number };
    created_at?: string;
    updated_at?: string;
    snippet?: string;
    content: string;
    image?: string;
}
export interface CreateBlog {
    title: string;
    content: string;
    image: File;
}
export interface UpdateBlog {
    title: string;
    content: string;
    image: File;
}
export interface DeleteBlog {
    id: number;
}

