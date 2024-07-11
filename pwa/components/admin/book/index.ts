import { BooksList } from "./BooksList";
import { BooksCreate } from "./BooksCreate";
import { BooksEdit } from "./BooksEdit";
import { type Book } from "../../../types/Book";

const bookResourceProps = {
  list: BooksList,
  create: BooksCreate,
  edit: BooksEdit,
  hasShow: false,
  recordRepresentation: (record: Book) => `${record.title} - ${record.author}`,
};

export default bookResourceProps;
