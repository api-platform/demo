import React from 'react';
import { Route } from 'react-router-dom';
import { List, Create, Update, Show } from '../components/review/';

export default [
  <Route path="/reviews/create" component={Create} exact key="create" />,
  <Route path="/reviews/edit/:id" component={Update} exact key="update" />,
  <Route path="/reviews/show/:id" component={Show} exact key="show" />,
  <Route path="/reviews/" component={List} exact strict key="list" />,
  <Route path="/reviews/:page" component={List} exact strict key="page" />
];
