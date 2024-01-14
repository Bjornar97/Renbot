import type { AutoPost } from "./AutoPost";
import type { CommandType } from "./CommandType";
import type { UsableBy } from "./UsableBy";

export interface Command {
    id: number;
    command: string;
    response: string;
    enabled: boolean;
    cooldown: number;
    global_cooldown: number;
    type: CommandType;
    usable_by: UsableBy;
    severity: number;
    punish_reason: string;
    action: string;
    prepend_sender: boolean;
    auto_post_enabled: boolean;
    auto_post_id: number;
    parent_id: number | null;

    auto_post?: AutoPost;

    created_at: string;
    updated_at: string;

    parent?: Command;
    children?: Command[];
}
