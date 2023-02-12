export interface Command {
    id: number;
    command: string;
    response: string;
    enabled: boolean;
    cooldown: number;
    global_cooldown: number;
    type: "regular" | "punishable" | "special";
    usable_by: "moderators" | "subscribers" | "everyone";
    severity: number;
    punish_reason: string;
    action: string;

    created_at: string;
    updated_at: string;
}
