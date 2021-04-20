export class Book {
  public "@id"?: string;

  constructor(
    _id?: string,
    public id?: string,
    public isbn?: string,
    public title?: string,
    public description?: string,
    public author?: string,
    public publicationDate?: string,
    public reviews?: any,
    public cover?: string,
  ) {
    this["@id"] = _id;
  }
}
