import { type Description } from "./Description";
import { type Item } from "./Item";

export class Book {
  constructor(
    public description?: string | Description,
    public publish_date?: string,
    public covers?: Array<number>,
    public works?: Array<Item>,
  ) {
  }
}
