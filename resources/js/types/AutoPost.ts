export interface AutoPost {
    id: number;
    title: string;
    interval: number;
    interval_type: "seconds" | "minutes" | "hours" | "days";
    min_posts_between: number;
    last_post: string;

    created_at: string;
    updated_at: string;
}
