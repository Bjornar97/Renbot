import { Creator } from "./Creator";
import { Event } from "./Event";

export interface EventTeam {
    id: number;
    name: string;
    color: string | null;

    event?: Event;
    creator?: Creator;

    created_at: string;
    updated_at: string;
}
