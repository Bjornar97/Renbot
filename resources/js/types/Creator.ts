export interface Creator {
    id: number;
    name: string;
    youtube_url: string | null;
    twitch_url: string | null;
    x_url: string | null;
    image: string;

    image_url: string;

    deleted_at: string | null;
    created_at: string;
    updated_at: string;
}
