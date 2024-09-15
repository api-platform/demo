import {
  TextField,
  DateField,
  Datagrid,
  List,
  ListActions,
  EditButton,
  ShowButton,
  ReferenceInput,
  AutocompleteInput,
  WrapperField,
} from "react-admin";

import { BookField } from "./BookField";
import { RatingField } from "./RatingField";
import { RatingInput } from "./RatingInput";
import { type Book } from "../../../types/Book";
import { User } from "../../../types/User";

const bookQuery = (searchText: string) => {
  const values = searchText
    .split(" - ")
    .map((n) => n.trim())
    .filter((n) => n);
  const query = { title: values[0] };
  if (typeof values[1] !== "undefined") {
    // @ts-ignore
    query.author = values[1];
  }

  return query;
};

const filters = [
  <ReferenceInput name="book" reference="admin/books" source="book" key="book">
    <AutocompleteInput
      filterToQuery={bookQuery}
      optionText={(choice: Book): string =>
        `${choice.title} - ${choice.author}`
      }
      name="book"
      style={{ width: 300 }}
    />
  </ReferenceInput>,
  <ReferenceInput name="user" reference="admin/users" source="user" key="user">
    <AutocompleteInput
      filterToQuery={(searchText: string) => ({ name: searchText })}
      optionText={(choice: User): string => choice.name}
      name="user"
      style={{ width: 300 }}
    />
  </ReferenceInput>,
  <RatingInput name="rating" source="rating" key="rating" size="medium" />,
];

export const ReviewsList = () => (
  <List filters={filters} exporter={false} actions={<ListActions hasCreate={false} />} title="Reviews">
    <Datagrid>
      <TextField source="user.name" label="User" sortable={false} />
      <BookField source="book" sortable={false} />
      <DateField source="publishedAt" sortable={false} />
      <WrapperField label="Rating" sortable={false}>
        <RatingField />
      </WrapperField>
      <ShowButton />
      <EditButton />
    </Datagrid>
  </List>
);
