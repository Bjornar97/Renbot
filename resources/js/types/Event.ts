import { Creator } from "./Creator";
import { EventTeam } from "./EventTeam";

export interface Event {
    id: number;
    type: "mcc" | "charity-fundraising" | "twitch-rivals" | "other";
    title: string;
    description: string | null;
    event_url: string | null;
    start: string;
    end: string;

    participants?: (Creator & {
        pivot?: {
            event_team_id: number;
        };
    })[];
    teams?: EventTeam[];

    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}
