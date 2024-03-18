import { type Item } from "./item";
import { type Thumbnails } from "./Thumbnails";

export class Book implements Item {
  public "@id"?: string;

  constructor(
    public book: string,
    public title: string,
    public condition: string,
    public reviews: string,
    public author?: string,
    public rating?: number,
    _id?: string,
    public id?: string,
    public slug?: string,
    public images?: Thumbnails,
    public description?: string,
    public publicationDate?: string,
  ) {
    this["@id"] = _id;
  }
}
