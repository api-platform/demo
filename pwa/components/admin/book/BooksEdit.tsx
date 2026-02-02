import {EditGuesser} from "@api-platform/admin";
import {TopToolbar} from "react-admin";

import {BookForm} from "./BookForm";
import {ShowButton} from "./ShowButton";

const Actions = () => (
  <TopToolbar>
    <ShowButton />
  </TopToolbar>
);
export const BooksEdit = () => (
  <EditGuesser title="Edit book" actions={<Actions />}>
    <BookForm />
  </EditGuesser>
);
