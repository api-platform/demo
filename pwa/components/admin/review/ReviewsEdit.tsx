import { EditGuesser } from "@api-platform/admin";
import {
  AutocompleteInput,
  ReferenceInput,
  required,
  TextInput,
} from "react-admin";

import { type Book } from "../../../types/Book";
import { type Review } from "../../../types/Review";
import { RatingInput } from "./RatingInput";

const transform = (data: Review) => ({
  ...data,
  book: data.book["@id"],
  rating: Number(data.rating),
});

export const ReviewsEdit = () => (
  <EditGuesser title="Edit review" transform={transform}>
    <ReferenceInput name="book" reference="admin/books" source="book[@id]">
      <AutocompleteInput
        filterToQuery={(searchText: string) => ({ title: searchText })}
        optionText={(choice: Book): string =>
          `${choice.title} - ${choice.author}`
        }
        label="Book"
        style={{ width: 500 }}
        validate={required()}
      />
    </ReferenceInput>
    <TextInput
      multiline
      name="body"
      validate={required()}
      source="body"
      style={{ width: 500 }}
    />
    <RatingInput name="rating" validate={required()} source="rating" size="medium" style={{ width: 500 }}/>
  </EditGuesser>
);
