import { EditGuesser, type EditGuesserProps } from "@api-platform/admin";
import { AutocompleteInput, ReferenceInput, required, TextInput } from "react-admin";

import { type Book } from "@/types/Book";
import { type Review } from "@/types/Review";
import { RatingInput } from "@/components/admin/review/RatingInput";

const transform = (data: Review) => ({
  ...data,
  book: data.book["@id"],
  rating: Number(data.rating),
});

export const Edit = (props: EditGuesserProps) => (
  <EditGuesser {...props} title="Edit review" transform={transform}>
    <ReferenceInput name="book" validate={required()} reference="admin/books" source="book[@id]">
      <AutocompleteInput filterToQuery={(searchText: string) => ({ title: searchText })}
                         optionText={(choice: Book): string => `${choice.title} - ${choice.author}`}
                         label="Book" style={{ width: 500 }}/>
    </ReferenceInput>
    <TextInput multiline name="body" validate={required()} source="body" style={{ width: 500 }}/>
    <RatingInput name="rating" validate={required()} source="rating" size="medium" style={{ width: 500 }}/>
  </EditGuesser>
);
