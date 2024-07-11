import { required } from "react-admin";

import { ConditionInput } from "./ConditionInput";
import { BookInput } from "./BookInput";

export const BookForm = () => (
  <>
    <BookInput validate={required()} />
    <ConditionInput validate={required()} />
  </>
);
