export interface Quote {
    id: number;
    quote: string;
    said_by: string;
    said_at: string;

    deleted_at: string | null;
    created_at: string;
    updated_at: string;
}
