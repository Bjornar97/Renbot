import { StreamdaySlot } from "./StreamdaySlot";

export interface Streamday {
    id: number;
    start_date: string;
    end_date: string;

    streamday_slots?: StreamdaySlot[];

    created_at: string;
    updated_at: string;
}
