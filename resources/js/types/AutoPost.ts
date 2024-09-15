import { Command } from "./Command";

export interface AutoPost {
    id: number;
    title: string;
    enabled: boolean;
    interval: number;
    interval_type: "seconds" | "minutes" | "hours" | "days";
    min_posts_between: number;
    last_post: string;
    last_command_id: number;

    created_at: string;
    updated_at: string;

    chats_to_next: number;

    commands?: Command[];
}
