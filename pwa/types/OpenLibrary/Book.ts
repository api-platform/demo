import { type Description } from "@/types/OpenLibrary/Description";
import { type Item } from "@/types/OpenLibrary/Item";

export class Book {
  constructor(
    public description?: string | Description,
    public publish_date?: string,
    public covers?: Array<number>,
    public works?: Array<Item>,
  ) {
  }
}
