import { type ListGuesserProps } from "@api-platform/admin";
import {
  TextField,
  DateField,
  Datagrid,
  List as ReactAdminList,
  EditButton,
  ShowButton,
  ReferenceInput,
  AutocompleteInput,
} from "react-admin";

import { BookField } from "@/components/admin/review/BookField";
import { RatingField } from "@/components/admin/review/RatingField";
import { RatingInput } from "@/components/admin/review/RatingInput";
import { type Book } from "@/types/Book";
import { User } from "@/types/User";

const bookQuery = (searchText: string) => {
  const values = searchText.split(" - ").map(n => n.trim()).filter(n => n);
  const query = { title: values[0] };
  if (typeof values[1] !== "undefined") {
    // @ts-ignore
    query.author = values[1];
  }

  return query;
};

const filters = [
  <ReferenceInput name="book" reference="admin/books" source="book" key="book">
    <AutocompleteInput filterToQuery={bookQuery}
                       optionText={(choice: Book): string => `${choice.title} - ${choice.author}`}
                       name="book" style={{ width: 300 }}/>
  </ReferenceInput>,
  <ReferenceInput name="user" reference="admin/users" source="user" key="user">
    <AutocompleteInput filterToQuery={(searchText: string) => ({ name: searchText })}
                       optionText={(choice: User): string => choice.name}
                       name="user" style={{ width: 300 }}/>
  </ReferenceInput>,
  <RatingInput name="rating" source="rating" key="rating" size="medium"/>,
];

export const List = (props: ListGuesserProps) => (
  <ReactAdminList {...props} filters={filters} exporter={false} hasCreate={false} title="Reviews">
    <Datagrid>
      <TextField source="user.name" label="User" sortable={false}/>
      <BookField source="book" sortable={false}/>
      <DateField source="publishedAt" sortable={false}/>
      <RatingField source="rating" sortable={false}/>
      <ShowButton/>
      <EditButton/>
    </Datagrid>
  </ReactAdminList>
);
