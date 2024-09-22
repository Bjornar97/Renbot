export interface BlockedTerm {
    id: number;
    twitch_id: string;
    term: string;
    comment: string | null;

    created_at: string;
    updated_at: string;
    expires_at: string | null;
}
