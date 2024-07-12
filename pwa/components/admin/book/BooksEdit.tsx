import { EditGuesser, type EditGuesserProps } from "@api-platform/admin";
import { TopToolbar } from "react-admin";

import { BookForm } from "./BookForm";
import { ShowButton } from "./ShowButton";

// @ts-ignore
const Actions = () => (
  <TopToolbar>
    <ShowButton />
  </TopToolbar>
);
export const BooksEdit = () => (
  // @ts-ignore
  <EditGuesser title="Edit book" actions={<Actions />}>
    <BookForm />
  </EditGuesser>
);
