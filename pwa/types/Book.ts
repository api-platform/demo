import { Item } from "./item";

export class Book implements Item {
  public "@id"?: string;

  constructor(
    _id?: string,
    public isbn?: string,
    public title?: string,
    public description?: string,
    public author?: string,
    public publicationDate?: Date,
    public reviews?: any,
    public cover?: string
  ) {
    this["@id"] = _id;
  }
}
