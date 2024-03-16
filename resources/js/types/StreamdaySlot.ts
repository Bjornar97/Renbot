import { Creator } from "./Creator";

export interface StreamdaySlot {
    id: number;
    creator_id: number;

    start_at: string;
    end_at: string;

    creator: Creator;
}
