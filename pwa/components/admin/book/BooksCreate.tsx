import { CreateGuesser, type CreateGuesserProps } from "@api-platform/admin";

import { BookForm } from "./BookForm";

export const BooksCreate = (props: CreateGuesserProps) => (
  <CreateGuesser {...props} title="Create book">
    <BookForm />
  </CreateGuesser>
);
